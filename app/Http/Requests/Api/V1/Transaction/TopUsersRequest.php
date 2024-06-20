<?php

namespace App\Http\Requests\Api\V1\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class TopUsersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_limit'        => ['integer', 'min:1', 'max:10'],
            'transaction_limit' => ['integer', 'min:1', 'max:50'],
            'since'             => ['date','after_or_equal:-10 minutes' , 'before_or_equal:now']
        ];
    }
}
