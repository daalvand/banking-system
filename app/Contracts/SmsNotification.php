<?php

namespace App\Contracts;

use App\ValueObjects\SmsMessage;

interface SmsNotification
{

    public function toSms($notifiable): SmsMessage;
}
