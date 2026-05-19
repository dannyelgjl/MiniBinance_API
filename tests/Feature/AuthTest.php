<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_receives_initial_wallet(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Daniel',
            'email' => 'daniel@example.com',
            'password' => 'password123',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('user.email', 'daniel@example.com')
            ->assertJsonPath('wallet.brl_balance_cents', 1_000_000)
            ->assertJsonPath('wallet.btc_balance_satoshis', 0);

        $this->assertDatabaseHas('wallets', [
            'brl_balance_cents' => 1_000_000,
            'btc_balance_satoshis' => 0,
        ]);
    }

    public function test_user_can_login_and_access_me(): void
    {
        User::factory()->create([
            'email' => 'trader@example.com',
            'password' => 'password123',
        ]);

        $login = $this->postJson('/api/login', [
            'email' => 'trader@example.com',
            'password' => 'password123',
        ]);

        $token = $login->assertOk()->json('access_token');

        $this->withToken($token)
            ->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('email', 'trader@example.com');
    }

    public function test_me_requires_authentication(): void
    {
        $this->getJson('/api/me')->assertUnauthorized();
    }
}
