<?php

namespace App\Http\Resources\Api\V1\Transaction;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class Transfer extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'status'                  => $this->status,
            'message'                 => $this->message,
            'amount'                  => $this->amount,
            'source_card_number'      => $this->sourceCardNumber,
            'destination_card_number' => $this->destinationCardNumber,
            'transaction_fee'         => $this->transactionFee,
        ];
    }
}
