<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class MarketTest extends TestCase
{
    public function test_btc_market_returns_price_inside_expected_range(): void
    {
        Cache::forget('market:btc:brl');

        $response = $this->getJson('/api/market/btc');

        $response
            ->assertOk()
            ->assertJsonPath('symbol', 'BTCBRL')
            ->assertJsonStructure([
                'asset',
                'currency',
                'price',
                'price_formatted',
                'price_cents',
                'cached_for_seconds',
            ]);

        $this->assertGreaterThanOrEqual(20_000_000, $response->json('price_cents'));
        $this->assertLessThanOrEqual(30_000_000, $response->json('price_cents'));
    }
}
