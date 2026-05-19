<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TradeTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_wallet(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->getJson('/api/wallet')
            ->assertOk()
            ->assertJsonPath('brl_balance_cents', 1_000_000)
            ->assertJsonPath('btc_balance_satoshis', 0);
    }

    public function test_user_can_buy_btc_with_available_brl_balance(): void
    {
        Cache::put('market:btc:brl', 25_000_000, now()->addMinute());

        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->postJson('/api/trade/buy', ['amount_brl' => 1000])
            ->assertCreated()
            ->assertJsonPath('wallet.brl_balance_cents', 900_000)
            ->assertJsonPath('wallet.btc_balance_satoshis', 400_000)
            ->assertJsonPath('transaction.type', 'buy');

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'type' => 'buy',
            'brl_amount_cents' => 100_000,
            'btc_amount_satoshis' => 400_000,
            'btc_price_cents' => 25_000_000,
        ]);
    }

    public function test_user_can_sell_btc_with_available_btc_balance(): void
    {
        Cache::put('market:btc:brl', 25_000_000, now()->addMinute());

        $user = User::factory()->create();
        $user->wallet()->update([
            'btc_balance_satoshis' => 1_000_000,
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/trade/sell', ['amount_btc' => '0.005'])
            ->assertCreated()
            ->assertJsonPath('wallet.brl_balance_cents', 1_125_000)
            ->assertJsonPath('wallet.btc_balance_satoshis', 500_000)
            ->assertJsonPath('transaction.type', 'sell');
    }

    public function test_buy_validates_brl_balance(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->postJson('/api/trade/buy', ['amount_brl' => 10_001])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('amount_brl');
    }

    public function test_sell_validates_btc_balance(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->postJson('/api/trade/sell', ['amount_btc' => '0.00000001'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('amount_btc');
    }

    public function test_user_can_list_transactions(): void
    {
        Cache::put('market:btc:brl', 25_000_000, now()->addMinute());

        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->postJson('/api/trade/buy', ['amount' => 1000])
            ->assertCreated();

        $this->getJson('/api/transactions')
            ->assertOk()
            ->assertJsonPath('data.0.type', 'buy')
            ->assertJsonPath('data.0.brl_amount_cents', 100_000)
            ->assertJsonPath('data.0.btc_price_cents', 25_000_000);
    }
}
