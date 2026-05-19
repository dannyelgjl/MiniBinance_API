<?php

namespace App\Http\Resources;

use App\Services\AssetFormatter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'brl_balance' => (float) AssetFormatter::formatBrl($this->brl_balance_cents),
            'brl_balance_formatted' => AssetFormatter::formatBrl($this->brl_balance_cents),
            'brl_balance_cents' => $this->brl_balance_cents,
            'btc_balance' => (float) AssetFormatter::formatBtc($this->btc_balance_satoshis),
            'btc_balance_formatted' => AssetFormatter::formatBtc($this->btc_balance_satoshis),
            'btc_balance_satoshis' => $this->btc_balance_satoshis,
        ];
    }
}
