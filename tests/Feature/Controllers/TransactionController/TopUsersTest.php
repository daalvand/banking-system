<?php

namespace Tests\Feature\Controllers\TransactionController;

use App\Models\Account;
use App\Models\Card;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TopUsersTest extends TestCase
{
    use LazilyRefreshDatabase;

    #[Test]
    public function it_should_return_top_users_with_spent_transactions()
    {
        $users = User::factory()->count(5)->create();
        foreach ($users as $user) {
            $card = Card::factory()->for(Account::factory()->for($user))->create(['balance' => 10000]);
            Transaction::factory(20)->create(['source_card_id' => $card->id]);
        }

        $response = $this->apiRequest();
        $response->assertOk();
        $response->assertJsonStructure([
            '*' => [
                'id', 'name', 'transactions' => ['*' => ['id', 'source_card_id', 'destination_card_id', 'amount']]
            ],
        ], $response->json('data'));

        $response->assertJsonCount(3, 'data');
        $response->assertJsonCount(10, 'data.0.transactions');
        $response->assertJsonCount(10, 'data.1.transactions');
        $response->assertJsonCount(10, 'data.2.transactions');
    }

    #[Test]
    public function it_should_return_top_users_with_received_transactions()
    {
        $users = User::factory()->count(5)->create();
        foreach ($users as $user) {
            $card = Card::factory()->for(Account::factory()->for($user))->create(['balance' => 10000]);
            Transaction::factory(20)->create(['destination_card_id' => $card->id]);
        }

        $response = $this->apiRequest();
        $response->assertOk();
        $response->assertJsonStructure([
            '*' => [
                'id', 'name', 'transactions' => ['*' => ['id', 'source_card_id', 'destination_card_id', 'amount']]
            ],
        ], $response->json('data'));

        $response->assertJsonCount(3, 'data');
        $response->assertJsonCount(10, 'data.0.transactions');
        $response->assertJsonCount(10, 'data.1.transactions');
        $response->assertJsonCount(10, 'data.2.transactions');
    }

    #[Test]
    public function it_should_not_return_users_with_old_transactions()
    {
        $users           = User::factory()->count(5)->create();
        $maxMin          = config('top-users.max_since_minutes');
        $transactionTime = now()->subMinutes($maxMin + 1);
        foreach ($users as $user) {
            $card = Card::factory()->for(Account::factory()->for($user))->create(['balance' => 10000]);
            Transaction::factory(20)->create(['source_card_id' => $card->id, 'created_at' => $transactionTime]);
        }

        $response = $this->apiRequest();
        $response->assertOk();
        $response->assertJson(['data' => []]);
    }

    protected function apiRequest(): TestResponse
    {
        return $this->getJson('/api/v1/transactions/top-users');
    }
}
