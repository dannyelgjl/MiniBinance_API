<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class BtcMarketService
{
    private const CACHE_KEY = 'market:btc:brl';

    public function currentPriceCents(): int
    {
        $ttl = max(1, (int) config('trading.market.ttl_seconds'));

        return (int) Cache::remember(self::CACHE_KEY, now()->addSeconds($ttl), function (): int {
            return random_int(
                (int) config('trading.market.min_price_cents'),
                (int) config('trading.market.max_price_cents')
            );
        });
    }
}
