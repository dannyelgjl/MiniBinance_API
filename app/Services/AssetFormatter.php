<?php

namespace App\Services;

use InvalidArgumentException;

class AssetFormatter
{
    public const CENTS_PER_BRL = 100;
    public const SATOSHIS_PER_BTC = 100_000_000;

    public static function brlToCents(mixed $amount): int
    {
        return self::decimalToUnits($amount, 2);
    }

    public static function btcToSatoshis(mixed $amount): int
    {
        return self::decimalToUnits($amount, 8);
    }

    public static function formatBrl(int $cents): string
    {
        return self::formatUnits($cents, 2);
    }

    public static function formatBtc(int $satoshis): string
    {
        return self::formatUnits($satoshis, 8);
    }

    private static function decimalToUnits(mixed $amount, int $scale): int
    {
        $value = str_replace(',', '.', trim((string) $amount));

        if (! preg_match('/^\d+(\.\d{1,'.$scale.'})?$/', $value)) {
            throw new InvalidArgumentException('Invalid decimal amount.');
        }

        [$integer, $decimal] = array_pad(explode('.', $value, 2), 2, '');

        return ((int) $integer * (10 ** $scale)) + (int) str_pad($decimal, $scale, '0');
    }

    private static function formatUnits(int $units, int $scale): string
    {
        $factor = 10 ** $scale;
        $integer = intdiv($units, $factor);
        $decimal = $units % $factor;

        return $integer.'.'.str_pad((string) $decimal, $scale, '0', STR_PAD_LEFT);
    }
}
