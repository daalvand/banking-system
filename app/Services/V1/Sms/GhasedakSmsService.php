<?php

namespace App\Services\V1\Sms;

use App\Contracts\SmsService;
use App\Exceptions\SmsException;
use App\ValueObjects\SmsMessage;

class GhasedakSmsService implements SmsService
{
    public function __construct()
    {
    }

    /**
     * @throws SmsException
     */
    public function send(SmsMessage $message): void
    {
        throw new SmsException('this driver is not implemented yet');
    }
}
