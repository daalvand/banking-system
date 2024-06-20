<?php

namespace App\Http\Controllers\Api\V1;

use App\Contracts\TransactionService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\TransactionRequest;
use App\Models\Card;

class TransactionController extends Controller
{
    protected TransactionService $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function transfer(TransactionRequest $request)
    {
        $sourceCard      = $request->validated('source_card');
        $destinationCard = $request->validated('destination_card');
        $result          = $this->transactionService->transfer($sourceCard, $destinationCard, $request->amount);
        return response()->json($result);
    }

    public function topTransactions()
    {
        // Implement ...
    }
}
