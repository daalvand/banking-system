<?php

namespace App\Notifications;

use App\Channels\SmsChannel;
use App\Contracts\SmsNotification;
use App\ValueObjects\SmsMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TransferRecipientNotification extends Notification implements ShouldQueue, SmsNotification
{
    use Queueable;

    public function __construct(
        protected float $newBalance,
        protected float $amount,
        protected string $sourceCardNumber,
        protected string $destinationCardNumber
    ) {}

    public function via($notifiable): array
    {
        return [SmsChannel::class];
    }

    public function toSms($notifiable): SmsMessage
    {
        $message = __('sms.recipient_transfer_message', [
            'amount'      => number_format($this->amount, 2),
            'new_balance' => number_format($this->newBalance, 2),
            'source_card' => $this->sourceCardNumber,
            'dest_card'   => $this->destinationCardNumber
        ]);

        return new SmsMessage($message, config('sms.sender_number'), $notifiable->phone);
    }
}
