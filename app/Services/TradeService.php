<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TradeService
{
    public function __construct(private readonly BtcMarketService $market)
    {
    }

    public function buy(User $user, int $brlCents): Transaction
    {
        return DB::transaction(function () use ($user, $brlCents): Transaction {
            $wallet = $this->lockedWallet($user);

            if ($wallet->brl_balance_cents < $brlCents) {
                throw ValidationException::withMessages([
                    'amount_brl' => ['Saldo em BRL insuficiente.'],
                ]);
            }

            $priceCents = $this->market->currentPriceCents();
            $btcSatoshis = intdiv($brlCents * AssetFormatter::SATOSHIS_PER_BTC, $priceCents);

            if ($btcSatoshis <= 0) {
                throw ValidationException::withMessages([
                    'amount_brl' => ['Valor muito baixo para comprar BTC no preço atual.'],
                ]);
            }

            $wallet->brl_balance_cents -= $brlCents;
            $wallet->btc_balance_satoshis += $btcSatoshis;
            $wallet->save();

            return $user->transactions()->create([
                'type' => TransactionType::Buy->value,
                'brl_amount_cents' => $brlCents,
                'btc_amount_satoshis' => $btcSatoshis,
                'btc_price_cents' => $priceCents,
            ]);
        });
    }

    public function sell(User $user, int $btcSatoshis): Transaction
    {
        return DB::transaction(function () use ($user, $btcSatoshis): Transaction {
            $wallet = $this->lockedWallet($user);

            if ($wallet->btc_balance_satoshis < $btcSatoshis) {
                throw ValidationException::withMessages([
                    'amount_btc' => ['Saldo em BTC insuficiente.'],
                ]);
            }

            $priceCents = $this->market->currentPriceCents();
            $brlCents = intdiv($btcSatoshis * $priceCents, AssetFormatter::SATOSHIS_PER_BTC);

            if ($brlCents <= 0) {
                throw ValidationException::withMessages([
                    'amount_btc' => ['Valor muito baixo para vender BTC no preço atual.'],
                ]);
            }

            $wallet->btc_balance_satoshis -= $btcSatoshis;
            $wallet->brl_balance_cents += $brlCents;
            $wallet->save();

            return $user->transactions()->create([
                'type' => TransactionType::Sell->value,
                'brl_amount_cents' => $brlCents,
                'btc_amount_satoshis' => $btcSatoshis,
                'btc_price_cents' => $priceCents,
            ]);
        });
    }

    private function lockedWallet(User $user): Wallet
    {
        return Wallet::query()
            ->where('user_id', $user->id)
            ->lockForUpdate()
            ->firstOrFail();
    }
}
