<?php

namespace App\ValueObjects;

use Illuminate\Contracts\Support\Arrayable;

readonly class TransactionResult
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
}
