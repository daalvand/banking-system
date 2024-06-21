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
        $maxUserLimit = config('top-users.max_user_limit');
        $maxTrxLimit  = config('top-users.max_transaction_limit');
        $maxMinuts    = config('top-users.max_since_minutes');
        return [
            'user_limit'        => ['integer', 'min:1', "max:$maxUserLimit"],
            'transaction_limit' => ['integer', 'min:1', "max:$maxTrxLimit"],
            'since_minutes'     => ['date', "after_or_equal:" . now()->subMinutes($maxMinuts), 'before_or_equal:now']
        ];
    }
}
