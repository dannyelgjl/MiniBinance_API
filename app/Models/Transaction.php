<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'brl_amount_cents',
        'btc_amount_satoshis',
        'btc_price_cents',
    ];

    protected function casts(): array
    {
        return [
            'brl_amount_cents' => 'integer',
            'btc_amount_satoshis' => 'integer',
            'btc_price_cents' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
