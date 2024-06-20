<?php

namespace App\Providers;

use App\Contracts\TransactionRepository as TransactionRepositoryContract;
use App\Contracts\TransactionService as TransactionServiceContract;
use App\Repositories\TransactionRepository;
use App\Services\V1\TransactionService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(TransactionServiceContract::class, TransactionService::class);
        $this->app->bind(TransactionRepositoryContract::class, TransactionRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->apiRateLimit();
    }

    private function apiRateLimit(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
