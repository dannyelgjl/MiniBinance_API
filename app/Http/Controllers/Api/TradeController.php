<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Trade\BuyTradeRequest;
use App\Http\Requests\Trade\SellTradeRequest;
use App\Http\Resources\TransactionResource;
use App\Http\Resources\WalletResource;
use App\Services\AssetFormatter;
use App\Services\TradeService;
use Illuminate\Http\JsonResponse;

class TradeController extends Controller
{
    public function buy(BuyTradeRequest $request, TradeService $trades): JsonResponse
    {
        $transaction = $trades->buy(
            user: $request->user(),
            brlCents: AssetFormatter::brlToCents($request->validated('amount_brl'))
        );

        $wallet = $request->user()->wallet()->firstOrFail();

        return response()->json([
            'message' => 'Compra realizada com sucesso.',
            'wallet' => new WalletResource($wallet),
            'transaction' => new TransactionResource($transaction),
        ], 201);
    }

    public function sell(SellTradeRequest $request, TradeService $trades): JsonResponse
    {
        $transaction = $trades->sell(
            user: $request->user(),
            btcSatoshis: AssetFormatter::btcToSatoshis($request->validated('amount_btc'))
        );

        $wallet = $request->user()->wallet()->firstOrFail();

        return response()->json([
            'message' => 'Venda realizada com sucesso.',
            'wallet' => new WalletResource($wallet),
            'transaction' => new TransactionResource($transaction),
        ], 201);
    }
}
