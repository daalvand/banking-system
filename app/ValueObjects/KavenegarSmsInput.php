<?php

namespace App\ValueObjects;

readonly class KavenegarSmsInput
{
    public function __construct(
        public string $receptor,
        public string $message,
        public ?string $sender = null,
        public ?int $date = null,
        public ?string $type = null,
        public ?int $localid = null,
        public ?int $hide = null
    ) {
    }

    public function toArray(): array
    {
        return array_filter(get_object_vars($this), fn($value) => !is_null($value));
    }
}
