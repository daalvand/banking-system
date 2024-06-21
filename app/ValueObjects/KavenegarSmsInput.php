<?php

namespace App\ValueObjects;

readonly class KavenegarSmsInput
{
    public function __construct(
        public string $receptor,
        public string $message,
        public string $sender,
    ) {
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
