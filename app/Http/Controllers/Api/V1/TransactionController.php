<?php

namespace App\Http\Controllers\Api\V1;

use App\Contracts\TransactionRepository;
use App\Contracts\TransactionService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Transaction\TopUsersRequest;
use App\Http\Requests\Api\V1\Transaction\TransferRequest;
use App\Http\Resources\Api\V1\Transaction\Transfer;

class TransactionController extends Controller
{
    public function transfer(TransferRequest $request, TransactionService $transactionService): Transfer
    {
        $sourceCard      = $request->validated('source_card');
        $destinationCard = $request->validated('destination_card');
        $result          = $transactionService->transfer($sourceCard, $destinationCard, $request->amount);
        return new Transfer($result);
    }

    public function topUsers(TopUsersRequest $request, TransactionRepository $transactionRepository)
    {
        $userLimit        = $request->validated('user_limit', config('top-users.max_user_limit'));
        $transactionLimit = $request->validated('transaction_limit', config('top-users.max_transaction_limit'));
        $since            = $request->validated('since', now()->subMinutes(config('top-users.max_since_minutes')));
        $topUsers         = $transactionRepository->getTopUsersWithTransactions($userLimit, $transactionLimit, $since);
        return response()->json(['data' => $topUsers]);
    }
}
