<?php

namespace App\Support;

/**
 * Formatage institutionnel des montants budgétaires (FCFA).
 */
final class MoneyFormatter
{
    public static function format(
        mixed $amount,
        int $decimals = 0,
        string $currency = 'FCFA',
        bool $showCurrency = true,
    ): string {
        $value = number_format((float) $amount, $decimals, ',', ' ');

        return $showCurrency ? "{$value} {$currency}" : $value;
    }

    public static function formatCompact(mixed $amount): string
    {
        $n = (float) $amount;
        if (abs($n) >= 1_000_000_000) {
            return number_format($n / 1_000_000_000, 1, ',', ' ').' Mrd';
        }
        if (abs($n) >= 1_000_000) {
            return number_format($n / 1_000_000, 1, ',', ' ').' M';
        }

        return self::format($n, 0, 'FCFA', true);
    }
}
