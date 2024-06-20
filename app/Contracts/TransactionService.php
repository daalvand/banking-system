<?php

namespace App\Contracts;

namespace App\Contracts;

use App\Models\Card;
use App\ValueObjects\TransactionResult;

interface TransactionService
{
    public function transfer(int $sourceCard, int $destinationCard, float $amount): TransactionResult;
}
