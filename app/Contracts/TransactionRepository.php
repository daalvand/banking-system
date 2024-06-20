<?php

namespace App\Contracts;

use DateTimeInterface;
use Illuminate\Support\Collection;

interface TransactionRepository
{
    public function getTopUsersWithTransactions(int $userLimit, int $trLimit, DateTimeInterface $since): Collection;
}
