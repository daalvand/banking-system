<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Card;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Account>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'source_card_id'      => Card::factory(),
            'destination_card_id' => Card::factory(),
            'amount'              => $this->faker->randomFloat(2, 1000, 50000000),
        ];
    }
}
