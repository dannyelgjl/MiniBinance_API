<?php

namespace Tests\Unit;

use App\Services\AssetFormatter;
use PHPUnit\Framework\TestCase;

class AssetFormatterTest extends TestCase
{
    public function test_it_converts_brl_to_cents(): void
    {
        $this->assertSame(100_050, AssetFormatter::brlToCents('1000.50'));
    }

    public function test_it_converts_btc_to_satoshis(): void
    {
        $this->assertSame(123_456, AssetFormatter::btcToSatoshis('0.00123456'));
    }

    public function test_it_formats_balances(): void
    {
        $this->assertSame('10000.00', AssetFormatter::formatBrl(1_000_000));
        $this->assertSame('0.00123456', AssetFormatter::formatBtc(123_456));
    }
}
