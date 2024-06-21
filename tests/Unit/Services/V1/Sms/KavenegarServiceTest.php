<?php

namespace Tests\Unit\Services\V1\Sms;

use App\Contracts\KavenegarClient as KavenegarClientContract;
use App\Exceptions\KavenegarException;
use App\Exceptions\SmsException;
use App\Services\V1\Sms\KavenegarService;
use App\ValueObjects\KavenegarSmsInput;
use App\ValueObjects\SmsMessage;
use Illuminate\Support\Facades\Http;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class KavenegarServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::fake();
    }

    #[Test]
    public function it_should_send_sms_successfully()
    {
        /** @var KavenegarClientContract&MockInterface $mockClient */
        $mockClient = $this->mock(KavenegarClientContract::class);
        $service    = new KavenegarService($mockClient);

        $message = $this->getSmsMessage();

        $mockClient->shouldReceive('sendSms')
            ->withArgs(function (KavenegarSmsInput $input) use ($message) {
                $this->assertEquals($message->to, $input->receptor);
                $this->assertEquals($message->content, $input->message);
                $this->assertEquals($message->from, $input->sender);
                return true;
            });

        $service->send($message);

        Http::assertNothingSent();
    }

    #[Test]
    public function it_should_throw_sms_exception_on_kavenegar_exception()
    {
        /** @var KavenegarClientContract&MockInterface $mockClient */
        $mockClient = $this->mock(KavenegarClientContract::class);
        $service    = new KavenegarService($mockClient);
        $message    = $this->getSmsMessage();

        $mockClient->shouldReceive('sendSms')
            ->andThrow(new KavenegarException('Kavenegar API error'));

        $this->expectException(SmsException::class);

        $service->send($message);
        Http::assertNothingSent();
    }

    protected function getSmsMessage(): SmsMessage
    {
        return new SmsMessage(
            content: 'Test SMS message',
            from: '02111111111',
            to: '09123456789'
        );
    }
}
