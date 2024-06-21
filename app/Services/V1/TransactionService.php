<?php

namespace App\Services\V1;

use App\Contracts\TransactionService as TransactionServiceContract;
use App\Models\Card;
use App\Models\Transaction;
use App\Notifications\TransferRecipientNotification;
use App\Notifications\TransferSenderNotification;
use App\ValueObjects\TransactionResult;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class TransactionService implements TransactionServiceContract
{

    private float $fee;

    public function __construct()
    {
        $this->fee = config('bank.transaction_fee');
    }

    public function transfer(int $sourceCard, int $destinationCard, float $amount): TransactionResult
    {
        return DB::transaction(function () use ($sourceCard, $destinationCard, $amount) {
            $source      = Card::where('card_number', $sourceCard)->lockForUpdate()->first();
            $destination = Card::where('card_number', $destinationCard)->lockForUpdate()->first();

            $this->validateSufficientFunds($source, $amount);
            $this->debitSourceCard($source, $amount);
            $this->creditDestinationCard($destination, $amount);
            $this->createFeeAndTransaction($source, $destination, $amount);
            $this->notify($source, $destination, $amount);
            $this->logTransaction($source, $destination, $amount);

            return new TransactionResult(
                status: 'success',
                message: 'Transfer successful',
                amount: $amount,
                sourceCardNumber: $source->card_number,
                destinationCardNumber: $destination->card_number,
                transactionFee: $this->fee
            );
        });
    }

    private function validateSufficientFunds(Card $source, float $amount): void
    {
        if ($source->balance < $amount + $this->fee) {
            throw ValidationException::withMessages([
                'source_card' => __('validation.insufficient_funds', [
                    'required'  => $amount + $this->fee,
                    'available' => $source->balance,
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

    private function logTransaction(Card $source, Card $destination, float $amount): void
    {
        Log::info('Transfer successful', [
            'source_card'      => $source->card_number,
            'destination_card' => $destination->card_number,
            'amount'           => $amount,
            'fee'              => $this->fee,
        ]);
    }

    private function notify(Card $source, Card $destination, float $amount): void
    {
        $source->account->user->notify(new TransferSenderNotification(
            $source->balance,
            $amount + $this->fee,
            $source->card_number,
            $destination->card_number
        ));
        $destination->account->user->notify(new TransferRecipientNotification(
                $destination->balance,
                $amount,
                $source->card_number,
                $destination->card_number,
            )
        );
    }

    private function createFeeAndTransaction(Card $source, Card $destination, float $amount): void
    {
        $transaction = Transaction::create([
            'source_card_id'      => $source->id,
            'destination_card_id' => $destination->id,
            'amount'              => $amount,
        ]);

        $transaction->fees()->create(['amount' => $this->fee]);
    }
}
