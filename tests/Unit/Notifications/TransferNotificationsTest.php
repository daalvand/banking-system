<?php

namespace Tests\Unit\Notifications;

use App\Channels\SmsChannel;
use App\Models\User;
use App\Notifications\TransferRecipientNotification;
use App\Notifications\TransferSenderNotification;
use App\ValueObjects\SmsMessage;
use Illuminate\Notifications\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TransferNotificationsTest extends TestCase
{

    protected string $sender;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sender = config('sms.sender_number');
    }

    #[Test]
    public function it_constructs_transfer_recipient_notification_correctly()
    {
        $notifiable            = User::factory()->create();
        $newBalance            = 5000;
        $amount                = 2500;
        $sourceCardNumber      = card_generator();
        $destinationCardNumber = card_generator();

        $notification = new TransferRecipientNotification($newBalance, $amount, $sourceCardNumber, $destinationCardNumber);

        $this->assertInstanceOf(Notification::class, $notification);
        $this->assertEquals([SmsChannel::class], $notification->via($notifiable));
        $smsMessage = $notification->toSms($notifiable);
        $this->assertInstanceOf(SmsMessage::class, $smsMessage);
        $this->assertEquals($this->sender, $smsMessage->from);
        $this->assertEquals($notifiable->phone, $smsMessage->to);

        $expectedMessage = __('sms.recipient_transfer_message', [
            'amount'      => number_format($amount, 2),
            'new_balance' => number_format($newBalance, 2),
            'dest_card'   => $destinationCardNumber,
            'source_card' => $sourceCardNumber,
        ]);
        $this->assertEquals($expectedMessage, $smsMessage->content);
    }

    #[Test]
    public function it_constructs_transfer_sender_notification_correctly()
    {
        $notifiable            = User::factory()->create();
        $remainingBalance      = 3000;
        $amount                = 1500;
        $sourceCardNumber      = card_generator();
        $destinationCardNumber = card_generator();

        $notification = new TransferSenderNotification($remainingBalance, $amount, $sourceCardNumber, $destinationCardNumber);

        $this->assertInstanceOf(Notification::class, $notification);
        $this->assertEquals([SmsChannel::class], $notification->via($notifiable));
        $smsMessage = $notification->toSms($notifiable);
        $this->assertInstanceOf(SmsMessage::class, $smsMessage);
        $this->assertEquals($this->sender, $smsMessage->from);
        $this->assertEquals($notifiable->phone, $smsMessage->to);

        $expectedMessage = __('sms.sender_transfer_message', [
            'amount'            => number_format($amount, 2),
            'remaining_balance' => number_format($remainingBalance, 2),
            'dest_card'         => $destinationCardNumber,
            'source_card'       => $sourceCardNumber,
        ]);
        $this->assertEquals($expectedMessage, $smsMessage->content);
    }
}
