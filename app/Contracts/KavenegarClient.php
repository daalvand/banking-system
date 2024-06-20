<?php

namespace App\Contracts;

use App\ValueObjects\KavenegarSmsInput;

interface KavenegarClient
{
    public function sendSms(KavenegarSmsInput $request): void;
}
