<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class BtcMarketService
{
    private const CACHE_KEY = 'market:btc:brl';

    /**
     * @return array{price_cents: int, price_change: float, price_history_cents: array<int, int>}
     */
    public function currentMarket(): array
    {
        $cachedMarket = Cache::get(self::CACHE_KEY);

        if (is_array($cachedMarket)) {
            return $cachedMarket;
        }

        if (is_numeric($cachedMarket)) {
            return $this->buildMarket((int) $cachedMarket);
        }

        $ttl = max(1, (int) config('trading.market.ttl_seconds'));
        $priceCents = random_int(
            (int) config('trading.market.min_price_cents'),
            (int) config('trading.market.max_price_cents')
        );
        $market = $this->buildMarket($priceCents);

        Cache::put(self::CACHE_KEY, $market, now()->addSeconds($ttl));

        return $market;
    }

    public function currentPriceCents(): int
    {
        return $this->currentMarket()['price_cents'];
    }

    /**
     * @return array{price_cents: int, price_change: float, price_history_cents: array<int, int>}
     */
    private function buildMarket(int $priceCents): array
    {
        $history = $this->buildHistory($priceCents);
        $previousPrice = $history[count($history) - 2] ?? $priceCents;
        $priceChange = $previousPrice > 0
            ? (($priceCents - $previousPrice) / $previousPrice) * 100
            : 0;

        return [
            'price_cents' => $priceCents,
            'price_change' => round($priceChange, 2),
            'price_history_cents' => $history,
        ];
    }

    /**
     * @return array<int, int>
     */
    private function buildHistory(int $priceCents): array
    {
        $minPriceCents = (int) config('trading.market.min_price_cents');
        $maxPriceCents = (int) config('trading.market.max_price_cents');
        $history = [];
        $currentPrice = $priceCents;

        for ($index = 0; $index < 11; $index++) {
            $currentPrice = max(
                $minPriceCents,
                min($maxPriceCents, $currentPrice + random_int(-350_000, 350_000))
            );
            array_unshift($history, $currentPrice);
        }

        $history[] = $priceCents;

        return $history;
    }
}
