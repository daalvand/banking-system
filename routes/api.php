<?php


use App\Http\Controllers\Api\V1\TransactionController;
use Illuminate\Support\Facades\Route;


Route::prefix('v1')->group(function () {
    Route::prefix('transactions')
        ->controller(TransactionController::class)
        ->group(function () {
            Route::get('top', 'topTransactions');
            Route::post('', 'transfer');
        });
});
