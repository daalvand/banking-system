<?php

namespace Tests\Unit\Channels;

use App\Channels\SmsChannel;
use App\Contracts\SmsService;
use App\Models\User;
use App\Notifications\TransferSenderNotification;
use Mockery\MockInterface;
use Tests\TestCase;

class SmsChannelTest extends TestCase
{
    /** @test */
    public function it_should_send_sms_notification()
    {
        /** @var SmsService&MockInterface $smsService */
        $smsService = $this->mock(SmsService::class);
        $smsChannel = new SmsChannel($smsService);

        $notifiable   = User::factory()->create();
        $notification = $this->getNotification();

        $smsService->shouldReceive('send')
            ->once()
            ->withArgs(function ($smsMessage) use ($notification, $notifiable) {
                $expectedSmsMessage = $notification->toSms($notifiable);
                $this->assertEquals($expectedSmsMessage, $smsMessage);
                return true;
            });

        $smsChannel->send($notifiable, $notification);
    }

    public function getNotification(): TransferSenderNotification
    {
        return new TransferSenderNotification(3000, 1500, card_generator(), card_generator());
    }
}
