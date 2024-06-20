<?php

namespace App\ValueObjects;

readonly class SmsMessage
{
    public function __construct(
        public string $content,
        public string $from,
        public string $to,
    ) {
    }
}
