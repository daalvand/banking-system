<?php

namespace App\Services\V1\Sms;

use App\Contracts\SmsService;
use App\Exceptions\KavenegarException;
use App\Exceptions\SmsException;
use App\ValueObjects\KavenegarSmsInput;
use App\ValueObjects\SmsMessage;
use \App\Contracts\KavenegarClient;

class KavenegarService implements SmsService
{
    public function __construct(
        private KavenegarClient $client
    ) {
    }

    /**
     * @throws SmsException
     */
    public function send(SmsMessage $message): void
    {
        $inputs = new KavenegarSmsInput($message->to, $message->content, $message->from);
        try {
            $this->client->sendSms($inputs);
        } catch (KavenegarException $e) {
            throw new SmsException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
