<?php

namespace App\Repositories;

use App\Contracts\TransactionRepository as TransactionRepositoryContract;
use App\Models\Transaction;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Support\Collection;

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

    private function getUserTransactions(int $userId, int $limit): Collection
    {
        return Transaction::forUser($userId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    private function getTopUsers(DateTimeInterface $since, int $limit): Collection
    {
        return User::withTransactionCountsSince($since)
            ->orderByDesc('transactions_count')
            ->limit($limit)
            ->get();
    }
}
