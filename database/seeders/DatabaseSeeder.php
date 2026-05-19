<?php

namespace Database\Seeders;

use App\Enums\TransactionType;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->updateOrCreate(
            ['email' => 'teste@example.com'],
            [
                'name' => 'Teste',
                'password' => 'passwordteste123',
            ],
        );

        Wallet::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'brl_balance_cents' => 926_000,
                'btc_balance_satoshis' => 300_000,
            ],
        );

        $user->transactions()->delete();

        $user->transactions()->create([
            'type' => TransactionType::Buy->value,
            'brl_amount_cents' => 100_000,
            'btc_amount_satoshis' => 400_000,
            'btc_price_cents' => 25_000_000,
            'created_at' => now()->subMinutes(10),
            'updated_at' => now()->subMinutes(10),
        ]);

        $user->transactions()->create([
            'type' => TransactionType::Sell->value,
            'brl_amount_cents' => 26_000,
            'btc_amount_satoshis' => 100_000,
            'btc_price_cents' => 26_000_000,
            'created_at' => now()->subMinutes(5),
            'updated_at' => now()->subMinutes(5),
        ]);
    }
}
