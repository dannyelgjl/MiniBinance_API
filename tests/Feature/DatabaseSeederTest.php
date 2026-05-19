<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_test_user_wallet_and_transactions(): void
    {
        $this->seed();

        $user = User::query()
            ->where('email', 'teste@example.com')
            ->with(['wallet', 'transactions'])
            ->firstOrFail();

        $this->assertSame('Teste', $user->name);
        $this->assertSame(926_000, $user->wallet->brl_balance_cents);
        $this->assertSame(300_000, $user->wallet->btc_balance_satoshis);
        $this->assertCount(2, $user->transactions);

        $this->postJson('/api/login', [
            'email' => 'teste@example.com',
            'password' => 'passwordteste123',
        ])->assertOk();
    }
}
