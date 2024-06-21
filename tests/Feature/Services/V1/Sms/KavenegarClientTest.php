<?php

namespace Tests\Feature\Services\V1\Sms;

use App\Exceptions\KavenegarException;
use App\Services\V1\Sms\KavenegarClient;
use App\ValueObjects\KavenegarSmsInput;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class KavenegarClientTest extends TestCase
{
    protected KavenegarClient $kavenegarClient;

    protected string $baseUrl;
    protected string $apiKey;
    protected int    $timeout;

    protected function setUp(): void
    {
        parent::setUp();
        $this->baseUrl         = config('sms.services.kavenegar.base_url');
        $this->apiKey          = config('sms.services.kavenegar.api_key');
        $this->timeout         = config('sms.services.kavenegar.timeout');
        $this->kavenegarClient = new KavenegarClient($this->baseUrl, $this->apiKey, $this->timeout);
    }

    #[Test]
    public function it_should_send_sms_successfully()
    {
        Http::fake([
            $this->baseUrl . '/*' => Http::response(),
        ]);

        $smsInput = $this->getValidKavenegarSmsInput();
        $this->kavenegarClient->sendSms($smsInput);
        $this->assertHttpRequestWasSent($smsInput);
    }

    #[Test]
    public function it_should_throw_kavenegar_exception_on_http_failure()
    {
        Http::fake([
            $this->baseUrl . '/*' => fn() => throw new ConnectionException('Connection error')
        ]);

        $smsInput = $this->getValidKavenegarSmsInput();
        $this->expectException(KavenegarException::class);
        $this->kavenegarClient->sendSms($smsInput);
        $this->assertHttpRequestWasSent($smsInput);
    }

    #[Test]
    #[TestWith([401])]
    #[TestWith([400])]
    #[TestWith([422])]
    #[TestWith([500])]
    public function it_should_throw_kavenegar_exception_on_invalid_http_status(int $status)
    {
        Http::fake([
            $this->baseUrl . '/*' => Http::response(status: $status)
        ]);

        $smsInput = $this->getValidKavenegarSmsInput();
        $this->expectException(KavenegarException::class);
        $this->kavenegarClient->sendSms($smsInput);
        $this->assertHttpRequestWasSent($smsInput);
    }

    protected function getValidKavenegarSmsInput(): KavenegarSmsInput
    {
        return new KavenegarSmsInput(
            receptor: '09123456789',
            message: 'Test SMS message',
            sender: '02111111111'
        );
    }

    protected function assertHttpRequestWasSent(KavenegarSmsInput $smsInput): void
    {
        Http::assertSent(function (Request $request) use ($smsInput) {
            $expectedData = [
                'message'  => $smsInput->message,
                'receptor' => $smsInput->receptor,
                'sender'   => $smsInput->sender,
            ];

            $expectedUrl = "$this->baseUrl/$this->apiKey/sms/send.json";

            $this->assertEquals($expectedUrl, $request->url());
            $this->assertEquals($expectedData, $request->data());
            $this->assertEquals('POST', $request->method());

            return true;
        });
    }
}
