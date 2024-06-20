<?php

namespace App\Providers;

use App\Contracts\KavenegarClient as KavenegarClientContract;
use App\Contracts\SmsService;
use App\Exceptions\InvalidSmsProviderException;
use App\Services\V1\Sms\GhasedakSmsService;
use App\Services\V1\Sms\KavenegarClient;
use App\Services\V1\Sms\KavenegarService;
use Illuminate\Support\ServiceProvider;

class SmsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SmsService::class, function () {
            $driver = config('sms.default_driver');
            return match ($driver) {
                'kavenegar'   => new KavenegarService(app(KavenegarClientContract::class)),
                'ghasedaksms' => new GhasedakSmsService,
                default       => throw new InvalidSmsProviderException("Unsupported SMS driver: $driver"),
            };
        });


        $this->app->bind(KavenegarClientContract::class, function () {
            $apiKey  = config('sms.services.kavenegar.api_key');
            $baseUrl = config('sms.services.kavenegar.base_url');
            $timeout = config('sms.services.kavenegar.timeout');
            return new KavenegarClient($baseUrl, $apiKey, $timeout);
        });
    }
}
