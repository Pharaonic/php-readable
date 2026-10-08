<?php

declare(strict_types=1);

namespace Pharaonic\Readable;

use InvalidArgumentException;
use Pharaonic\Readable\Support\Assert;
use Pharaonic\Readable\Support\Intl;

/**
 * Readable money amounts. Formatting only: no arithmetic and no exchange rates.
 *
 * Without a $locale the built-in formatter prints the ISO code before the amount ("USD 1,234.56")
 * using the currency's ISO 4217 minor units. With a $locale, ext-intl picks the symbol and layout.
 */
final class Money
{
    /**
     * ISO 4217 currencies whose minor unit is not 2 digits. Every other code uses 2.
     */
    private const FRACTION_DIGITS = [
        'BHD' => 3, 'BIF' => 0, 'CLF' => 4, 'CLP' => 0, 'DJF' => 0, 'GNF' => 0, 'IQD' => 3,
        'ISK' => 0, 'JOD' => 3, 'JPY' => 0, 'KMF' => 0, 'KRW' => 0, 'KWD' => 3, 'LYD' => 3,
        'OMR' => 3, 'PYG' => 0, 'RWF' => 0, 'TND' => 3, 'UGX' => 0, 'UYI' => 0, 'UYW' => 4,
        'VND' => 0, 'VUV' => 0, 'XAF' => 0, 'XOF' => 0, 'XPF' => 0,
    ];

    /**
     * Format an amount: (1234.5, 'USD') → "USD 1,234.50", (1234.5, 'JPY') → "JPY 1,235",
     * (1234.5, 'USD', locale: 'en_US') → "$1,234.50".
     *
     * $decimals overrides the currency's default number of fraction digits.
     */
    public static function format(
        int|float $amount,
        string $currency,
        ?int $decimals = null,
        ?string $locale = null
    ): string {
        Assert::finite($amount);
        $currency = self::currency($currency);

        if ($decimals !== null) {
            Assert::decimals($decimals);
        }

        if ($locale !== null) {
            // Like the built-in path, an amount that rounds to zero must not print as "-$0.00".
            if ($amount < 0 && round($amount, $decimals ?? self::fractionDigits($currency)) == 0) {
                $amount = 0;
            }

            $formatter = Intl::formatter($locale, 'CURRENCY');

            if ($decimals !== null) {
                Intl::fractionDigits($formatter, $decimals);
            }

            return Intl::result($formatter->formatCurrency($amount, $currency), $formatter);
        }

        $decimals ??= self::fractionDigits($currency);

        return self::signed($amount, Number::format(abs($amount), $decimals), $currency);
    }

    /**
     * Format an amount in short form: (1500000, 'USD') → "USD 1.5M". English suffixes only, see Number::compact().
     */
    public static function compact(int|float $amount, string $currency, int $decimals = 1): string
    {
        Assert::finite($amount);

        return self::signed($amount, Number::compact(abs($amount), $decimals), self::currency($currency));
    }

    /**
     * Get the ISO 4217 number of fraction digits for a currency: USD → 2, JPY → 0, KWD → 3.
     */
    public static function fractionDigits(string $currency): int
    {
        return self::FRACTION_DIGITS[self::currency($currency)] ?? 2;
    }

    private static function currency(string $currency): string
    {
        $code = strtoupper(trim($currency));

        if (preg_match('/^[A-Z]{3}$/', $code) !== 1) {
            throw new InvalidArgumentException(
                sprintf('"%s" is not a three-letter ISO 4217 currency code.', $currency)
            );
        }

        return $code;
    }

    private static function signed(int|float $amount, string $formatted, string $currency): string
    {
        // Only show a minus when the printed amount is not zero ("-0.00" would be misleading).
        $negative = $amount < 0 && strpbrk($formatted, '123456789') !== false;

        return ($negative ? '-' : '') . $currency . ' ' . $formatted;
    }
}
