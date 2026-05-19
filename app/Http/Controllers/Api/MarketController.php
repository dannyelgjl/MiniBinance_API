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
        $priceCents = $market->currentPriceCents();

        return response()->json([
            'symbol' => config('trading.market.symbol'),
            'asset' => 'BTC',
            'currency' => 'BRL',
            'price' => (float) AssetFormatter::formatBrl($priceCents),
            'price_formatted' => AssetFormatter::formatBrl($priceCents),
            'price_cents' => $priceCents,
            'cached_for_seconds' => config('trading.market.ttl_seconds'),
        ]);
    }
}
