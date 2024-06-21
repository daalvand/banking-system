<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Card;
use App\Models\Fee;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::factory()->count(10)->create();
        foreach ($users as $user) {
            $card = Card::factory()->for(Account::factory()->for($user))->create(['balance' => 10000]);
            Transaction::factory(20)->has(Fee::factory())->create(['source_card_id' => $card->id]);
        }
    }
}
