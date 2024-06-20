<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\Card;

class CardCreditSufficient implements ValidationRule
{
    protected float $amount;

    public function __construct(float $amount)
    {
        $this->amount = $amount;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $card = Card::query()->where('card_number', (int)$value)->first();
        if (!$card) {
            $fail(__('validation.exists', ['attribute' => $attribute]));
            return;
        }

        $transactionFee      = config('bank.transaction_fee');
        $totalAmountRequired = $this->amount + $transactionFee;

        if ($card->balance < $totalAmountRequired) {
            $fail(__('validation.insufficient_funds', [
                'required'  => $totalAmountRequired,
                'available' => $card->balance
            ]));
        }
    }
}
