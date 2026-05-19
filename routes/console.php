<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('mini-binance:about', function (): void {
    $this->info('Mini Binance API is ready.');
})->purpose('Show Mini Binance API status');
