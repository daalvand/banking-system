<?php

namespace App\Repositories;

use App\Contracts\TransactionRepository as TransactionRepositoryContract;
use App\Models\Transaction;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TransactionRepository implements TransactionRepositoryContract
{
    public function getTopUsersWithTransactions(int $userLimit, int $trLimit, DateTimeInterface $since): Collection
    {
        $topUsers = $this->getTopUsers($since, $userLimit);

        return $topUsers->map(function ($user) use ($trLimit) {
            $transactions = $this->getUserTransactions($user->id, $trLimit);
            return [
                'id'           => $user->id,
                'name'         => $user->name,
                'transactions' => $transactions,
            ];
        });
    }

    public function getUserTransactions(int $userId, int $limit): Collection
    {
        return Transaction::query()
            ->join('cards as source_cards', 'transactions.source_card_id', '=', 'source_cards.id')
            ->join('accounts as source_accounts', 'source_cards.account_id', '=', 'source_accounts.id')
            ->join('users as source_users', 'source_accounts.user_id', '=', 'source_users.id')
            ->leftJoin('cards as destination_cards', 'transactions.destination_card_id', '=', 'destination_cards.id')
            ->leftJoin('accounts as destination_accounts', 'destination_cards.account_id', '=', 'destination_accounts.id')
            ->leftJoin('users as destination_users', 'destination_accounts.user_id', '=', 'destination_users.id')
            ->where(function ($query) use ($userId) {
                $query->where('source_users.id', $userId)
                    ->orWhere('destination_users.id', $userId);
            })
            ->orderByDesc('transactions.created_at')
            ->limit($limit)
            ->get(['transactions.*', 'source_users.id as source_user_id', 'destination_users.id as destination_user_id']);
    }

    public function getTopUsers(DateTimeInterface $since, int $limit): Collection
    {
        return User::query()
            ->select('users.id', 'users.name', DB::raw('COUNT(t1.id) + COUNT(t2.id) as transactions_count'))
            ->join('accounts', 'users.id', '=', 'accounts.user_id')
            ->join('cards', 'accounts.id', '=', 'cards.account_id')
            ->leftJoin('transactions as t1', function ($join) use ($since) {
                $join->on('cards.id', '=', 't1.source_card_id')->where('t1.created_at', '>=', $since);
            })
            ->leftJoin('transactions as t2', function ($join) use ($since) {
                $join->on('cards.id', '=', 't2.destination_card_id')->where('t2.created_at', '>=', $since);
            })
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('transactions_count')
            ->limit($limit)
            ->havingRaw('COUNT(t1.id) + COUNT(t2.id) > 0')
            ->toBase()
            ->get();
    }
}
