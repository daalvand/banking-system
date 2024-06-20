<?php

namespace App\Services\V1;

use App\Contracts\TransactionService as TransactionServiceContract;
use App\Models\Card;
use App\ValueObjects\TransactionResult;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class TransactionService implements TransactionServiceContract
{
    public function transfer(int $sourceCard, int $destinationCard, float $amount): TransactionResult
    {
        return DB::transaction(function () use ($sourceCard, $destinationCard, $amount) {
            $source      = Card::where('card_number', $sourceCard)->lockForUpdate()->first();
            $destination = Card::where('card_number', $destinationCard)->lockForUpdate()->first();

            $this->validateSufficientFunds($source, $amount);
            $this->debitSourceCard($source, $amount);
            $this->creditDestinationCard($destination, $amount);

            $transactionFee = config('bank.transaction_fee');
            $this->logTransaction($source, $destination, $amount, $transactionFee);

            return new TransactionResult(
                status: 'success',
                message: 'Transfer successful',
                amount: $amount,
                sourceCardNumber: $source->card_number,
                destinationCardNumber: $destination->card_number,
                transactionFee: $transactionFee
            );
        });
    }

    private function validateSufficientFunds(Card $source, float $amount): void
    {
        $transactionFee = config('bank.transaction_fee');
        if (($source->balance - $transactionFee) < $amount) {
            throw ValidationException::withMessages([
                'source_card' => __('validation.insufficient_funds', [
                    'required'  => $amount,
                    'available' => $source->balance - $transactionFee,
                ])
            ]);
        }
    }

    private function debitSourceCard(Card $source, float $amount): void
    {
        $transactionFee  = config('bank.transaction_fee');
        $source->balance -= ($amount + $transactionFee);
        $source->save();
    }

    private function creditDestinationCard(Card $destination, float $amount): void
    {
        $destination->balance += $amount;
        $destination->save();
    }

    private function logTransaction(Card $source, Card $destination, float $amount, float $transactionFee): void
    {
        Log::info('Transfer successful', [
            'source_card'      => $source->card_number,
            'destination_card' => $destination->card_number,
            'amount'           => $amount,
            'fee'              => $transactionFee,
        ]);
    }
}
