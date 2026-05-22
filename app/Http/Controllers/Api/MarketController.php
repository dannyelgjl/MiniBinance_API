<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AssetFormatter;
use App\Services\BtcMarketService;
use Illuminate\Http\JsonResponse;

class MarketController extends Controller
{
    public function btc(BtcMarketService $market): JsonResponse
    {
        $btcMarket = $market->currentMarket();
        $priceCents = $btcMarket['price_cents'];

        return response()->json([
            'symbol' => config('trading.market.symbol'),
            'asset' => 'BTC',
            'currency' => 'BRL',
            'price' => (float) AssetFormatter::formatBrl($priceCents),
            'price_change' => $btcMarket['price_change'],
            'price_formatted' => AssetFormatter::formatBrl($priceCents),
            'price_cents' => $priceCents,
            'price_history' => array_map(
                fn (int $historyPriceCents): float => (float) AssetFormatter::formatBrl($historyPriceCents),
                $btcMarket['price_history_cents']
            ),
            'price_history_cents' => $btcMarket['price_history_cents'],
            'cached_for_seconds' => config('trading.market.ttl_seconds'),
        ]);
    }
}
