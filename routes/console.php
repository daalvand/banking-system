<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $res = app(\App\Contracts\TransactionRepository::class)->getTopUsersWithTransactions(3, 10, now()->subDay());
    dd($res->toArray());
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();
