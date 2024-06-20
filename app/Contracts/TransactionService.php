<?php

namespace App\Contracts;

namespace App\Contracts;

use App\Models\Card;

interface TransactionService
{
    public function transfer(Card $source, Card $destination, float $amount): void;
}
