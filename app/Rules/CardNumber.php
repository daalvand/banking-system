<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CardNumber implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_scalar($value) || !is_valid_card($value)) {
            $fail(__('validation.invalid_card_number', ['attribute' => $attribute]));
        }
    }
}
