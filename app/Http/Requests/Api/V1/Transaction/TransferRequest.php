<?php

namespace App\Http\Requests\Api\V1\Transaction;

use App\Rules\CardCreditSufficient;
use App\Rules\CardNumber;
use Illuminate\Foundation\Http\FormRequest;

class TransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function validationData(): array
    {
        // Making sure that inputs will be sent in the body
        return $this->post();
    }

    public function rules(): array
    {
        $amount = (int)$this->input('amount');
        return [
            'source_card'      => [
                'required',
                'digits:16',
                'different:destination_card',
                new CardNumber,
                new CardCreditSufficient($amount),
            ],
            'destination_card' => [
                'required',
                'digits:16',
                'different:source_card',
                new CardNumber,
                'exists:cards,card_number'
            ],
            'amount'           => [
                'required',
                'int',
                'min:' . config('bank.min_amount'),
                'max:' . config('bank.max_amount')
            ],
        ];
    }

    public function prepareForValidation(): void
    {
        $this->normalizeInputs();
    }

    protected function normalizeInputs(): void
    {
        $this->merge([
            'amount'           => normalize_amount((string)$this->post('amount')),
            'source_card'      => normalize_card((string)$this->post('source_card')),
            'destination_card' => normalize_card((string)$this->post('destination_card')),
        ]);
    }
}
