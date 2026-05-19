<?php

return [
    'initial_brl_cents' => (int) env('TRADING_INITIAL_BRL_CENTS', 1_000_000),
    'initial_btc_satoshis' => (int) env('TRADING_INITIAL_BTC_SATOSHIS', 0),
    'token_name' => env('TRADING_TOKEN_NAME', 'mobile'),
    'market' => [
        'symbol' => env('TRADING_BTC_SYMBOL', 'BTCBRL'),
        'min_price_cents' => (int) env('TRADING_BTC_MIN_PRICE_CENTS', 20_000_000),
        'max_price_cents' => (int) env('TRADING_BTC_MAX_PRICE_CENTS', 30_000_000),
        'ttl_seconds' => (int) env('TRADING_MARKET_TTL', 10),
    ],
];
