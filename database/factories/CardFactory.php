<?php

namespace Database\Factories;

use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Account>
 */
class CardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id'  => Account::factory(),
            'card_number' => card_generator(),
            'balance'     => $this->faker->numberBetween(100, 1000) * 1000,
        ];
    }
}
