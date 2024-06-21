<?php

namespace Tests\Feature\Controllers\TransactionController;

use App\Models\Card;
use App\Models\Transaction;
use App\Notifications\TransferRecipientNotification;
use App\Notifications\TransferSenderNotification;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class TransferTest extends TestCase
{
    use LazilyRefreshDatabase;

    public const int SOURCE_BALANCE      = 10000;
    public const int DESTINATION_BALANCE = 5000;

    protected Card  $sourceCard;
    protected Card  $destinationCard;
    protected float $fee;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sourceCard      = Card::factory()->create(['balance' => self::SOURCE_BALANCE]);
        $this->destinationCard = Card::factory()->create(['balance' => self::DESTINATION_BALANCE]);
        $this->fee             = config('bank.transaction_fee');
    }

    #[Test]
    public function it_should_transfer_funds_successfully()
    {
        Notification::fake();
        $source      = $this->sourceCard->card_number;
        $destination = $this->destinationCard->card_number;
        $amount      = 1000;
        $response    = $this->makeTransferRequest($source, $destination, $amount);

        $response->assertOk()->assertJson([
            "data" => [
                "status"                  => "success",
                "message"                 => "Transfer successful",
                "amount"                  => $amount,
                "source_card_number"      => $source,
                "destination_card_number" => $destination,
                "transaction_fee"         => $this->fee,
            ]
        ]);

        $this->assertSourceCardBalanceUpdated($amount);
        $this->assertDestinationCardBalanceUpdated($amount);
        $this->assertTransactionCreated($amount);
        $this->assertFeeCreated();
        $this->assertNotificationsSent();
    }

    #[Test]
    public function it_should_fail_if_send_nothing()
    {
        Notification::fake();
        $response = $this->makeTransferRequest(null, null, null);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'source_card'      => [__('validation.required', ['attribute' => 'source_card'])],
                'destination_card' => [__('validation.required', ['attribute' => 'destination_card'])],
                'amount'           => [__('validation.required', ['attribute' => 'amount'])],
            ]);

        Notification::assertNothingSent();
        $this->assertSourceCardBalanceNotChanged();
        $this->assertDestinationCardBalanceNotChanged();
    }

    #[Test]
    public function it_should_fail_if_amount_is_less_than_min()
    {
        $source      = $this->sourceCard->card_number;
        $destination = $this->destinationCard->card_number;
        $amount      = config('bank.min_amount') - 1;
        $response    = $this->makeTransferRequest($source, $destination, $amount);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'amount' => [__('validation.min.numeric', ['attribute' => 'amount', 'min' => config('bank.min_amount')])]
            ]);
    }

    #[Test]
    public function it_should_fail_if_amount_is_greater_than_max()
    {
        $source      = $this->sourceCard->card_number;
        $destination = $this->destinationCard->card_number;
        $amount      = config('bank.max_amount') + 1;
        $response    = $this->makeTransferRequest($source, $destination, $amount);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'amount' => [__('validation.max.numeric', ['attribute' => 'amount', 'max' => config('bank.max_amount')])]
            ]);
    }

    #[Test]
    public function it_should_fail_if_insufficient_funds()
    {
        $source      = $this->sourceCard->card_number;
        $destination = $this->destinationCard->card_number;
        $amount      = $this->sourceCard->balance + 100;
        $response    = $this->makeTransferRequest($source, $destination, $amount);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'source_card' => [__('validation.insufficient_funds', ['required' => $amount + $this->fee, 'available' => $this->sourceCard->balance])]
            ]);
    }

    #[Test]
    public function it_should_fail_if_card_not_found()
    {
        $source      = '1111222233334444';
        $destination = '6209444444443333';
        $amount      = 1000;
        $response    = $this->makeTransferRequest($source, $destination, $amount);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'source_card'      => [__('validation.exists', ['attribute' => 'source_card'])],
                'destination_card' => [__('validation.exists', ['attribute' => 'destination_card'])]
            ]);
    }

    #[Test]
    public function it_should_fail_if_cards_are_same()
    {
        $source      = $this->sourceCard->card_number;
        $destination = $this->sourceCard->card_number;
        $amount      = 1000;
        $response    = $this->makeTransferRequest($source, $destination, $amount);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'source_card'      => [__('validation.different', ['attribute' => 'source_card', 'other' => 'destination_card'])],
                'destination_card' => [__('validation.different', ['attribute' => 'destination_card', 'other' => 'source_card'])],
            ]);
    }

    #[Test]
    #[TestWith(['invalid_card_1', 'invalid_card_2', 'invalid_amount'])]
    #[TestWith(['1234123412341234', '1111222233334445', 1000.123])]
    public function it_should_fail_if_cards_are_invalid($source, $destination, $amount)
    {
        $response = $this->makeTransferRequest($source, $destination, $amount);
        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'source_card'      => [__('validation.exists', ['attribute' => 'source_card']), __('validation.invalid_card_number', ['attribute' => 'source_card'])],
                'destination_card' => [__('validation.exists', ['attribute' => 'destination_card']), __('validation.invalid_card_number', ['attribute' => 'destination_card'])],
                'amount'           => [__('validation.integer', ['attribute' => 'amount'])]
            ]);
    }

    protected function makeTransferRequest($source, $destination, $amount): TestResponse
    {
        return $this->postJson('/api/v1/transactions/transfer', [
            'source_card'      => $source,
            'destination_card' => $destination,
            'amount'           => $amount,
        ]);
    }

    protected function assertSourceCardBalanceUpdated($amount): void
    {
        $this->assertDatabaseHas('cards', [
            'id'      => $this->sourceCard->id,
            'balance' => self::SOURCE_BALANCE - ($amount + $this->fee)
        ]);
    }

    protected function assertDestinationCardBalanceUpdated($amount): void
    {
        $this->assertDatabaseHas('cards', [
            'id'      => $this->destinationCard->id,
            'balance' => self::DESTINATION_BALANCE + $amount
        ]);
    }

    protected function assertNotificationsSent(): void
    {
        Notification::assertCount(2);
        Notification::assertSentTimes(TransferSenderNotification::class, 1);
        Notification::assertSentTimes(TransferRecipientNotification::class, 1);
        Notification::assertSentTo($this->sourceCard->account->user, TransferSenderNotification::class);
        Notification::assertSentTo($this->destinationCard->account->user, TransferRecipientNotification::class);
    }

    protected function assertSourceCardBalanceNotChanged(): void
    {
        $this->assertDatabaseHas('cards', [
            'id'      => $this->sourceCard->id,
            'balance' => self::SOURCE_BALANCE
        ]);
    }

    protected function assertDestinationCardBalanceNotChanged(): void
    {
        $this->assertDatabaseHas('cards', [
            'id'      => $this->destinationCard->id,
            'balance' => self::DESTINATION_BALANCE
        ]);
    }

    private function assertTransactionCreated(int $amount): void
    {
        $this->assertDatabaseCount('transactions', 1);
        $this->assertDatabaseHas('transactions', [
            'amount'              => $amount,
            'source_card_id'      => $this->sourceCard->id,
            'destination_card_id' => $this->destinationCard->id
        ]);
    }

    private function assertFeeCreated(): void
    {
        $this->assertDatabaseCount('fees', 1);
        $this->assertDatabaseHas('fees', [
            'amount'         => $this->fee,
            'transaction_id' => Transaction::first()->id,
        ]);
    }
}
