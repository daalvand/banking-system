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

    public function topTransactions(TopUsersRequest $request, TransactionRepository $transactionRepository)
    {
        $userLimit        = $request->validated('user_limit', 3);
        $transactionLimit = $request->validated('transaction_limit', 10);
        $since            = $request->validated('since', now()->subMinutes(10));
        $topUsers         = $transactionRepository->getTopUsersWithTransactions($userLimit, $transactionLimit, $since);
        return response()->json(['data' => $topUsers]);
    }
}
