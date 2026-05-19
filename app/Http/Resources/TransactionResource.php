<?php

namespace App\Http\Resources;

use App\Services\AssetFormatter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'brl_amount' => (float) AssetFormatter::formatBrl($this->brl_amount_cents),
            'brl_amount_formatted' => AssetFormatter::formatBrl($this->brl_amount_cents),
            'brl_amount_cents' => $this->brl_amount_cents,
            'btc_amount' => (float) AssetFormatter::formatBtc($this->btc_amount_satoshis),
            'btc_amount_formatted' => AssetFormatter::formatBtc($this->btc_amount_satoshis),
            'btc_amount_satoshis' => $this->btc_amount_satoshis,
            'btc_price' => (float) AssetFormatter::formatBrl($this->btc_price_cents),
            'btc_price_formatted' => AssetFormatter::formatBrl($this->btc_price_cents),
            'btc_price_cents' => $this->btc_price_cents,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
