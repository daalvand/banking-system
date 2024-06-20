<?php

namespace App\Contracts;

use App\ValueObjects\SmsMessage;

interface SmsService
{
    public function send(SmsMessage $message): void;
}
