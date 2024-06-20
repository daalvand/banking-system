<?php

namespace App\Services\V1;

namespace App\Services\V1;

use App\Contracts\TransactionService as TransactionServiceContract;
use App\Models\Card;

class TransactionService implements TransactionServiceContract
{
    public function transfer(Card $source, Card $destination, float $amount): void
    {
    }
}

