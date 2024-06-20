<?php

namespace App\Services\V1\Sms;

use App\Contracts\KavenegarClient as KavenegarClientContract;
use App\ValueObjects\KavenegarSmsInput;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use App\Exceptions\KavenegarException;

class KavenegarClient implements KavenegarClientContract
{
    public function __construct(
        private string $baseUrl,
        private string $apiKey,
        private int    $timeout,
    ) {
    }

    /**
     * @throws KavenegarException
     */
    public function sendSms(KavenegarSmsInput $request): void
    {
        $params = $request->toArray();
        $url    = $this->getUrl('sms/send.json');
        try {
            $response = Http::timeout($this->timeout)->asJson()->acceptJson()->post($url, $params);
            if (!$response->successful()) {
                throw new KavenegarException($response->body(), $response->status(), $response->toException());
            }
        } catch (ConnectionException $exception) {
            throw new KavenegarException('Timeout Exception', 0, $exception);
        }
    }

    private function getUrl(string $path): string
    {
        return "$this->baseUrl/$this->apiKey/$path";
    }
}
