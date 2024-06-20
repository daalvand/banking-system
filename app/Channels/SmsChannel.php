<?php

namespace App\Channels;

use App\Contracts\SmsNotification;
use App\Contracts\SmsService;
use Illuminate\Notifications\Notification;

readonly class SmsChannel
{
    public function __construct(private SmsService $smsService)
    {
    }

    public function send($notifiable, Notification&SmsNotification $notification): void
    {
        $message = $notification->toSms($notifiable);
        $this->smsService->send($message);
    }
}
