<?php

namespace App\ValueObjects;

use Illuminate\Contracts\Support\Arrayable;

readonly class TransactionResult implements Arrayable
{
    public function __construct(
        public string $status,
        public string $message,
        public float  $amount,
        public string $sourceCardNumber,
        public string $destinationCardNumber,
        public float  $transactionFee
    ) {
    }

    public function toArray(): array
    {
        return [
            'status'                  => $this->status,
            'message'                 => $this->message,
            'amount'                  => $this->amount,
            'source_card_number'      => $this->sourceCardNumber,
            'destination_card_number' => $this->destinationCardNumber,
            'transaction_fee'         => $this->transactionFee,
        ];
    }
}
