<?php

return [
    'min_amount'      => env('BANK_MIN_AMOUNT', 1000),
    'max_amount'      => env('BANK_MAX_AMOUNT', 50000000),
    'transaction_fee' => env('BANK_TRANSACTION_FEE', 500),
];
