<?php

declare(strict_types=1);

namespace Pharaonic\Readable;

use InvalidArgumentException;
use Pharaonic\Readable\Support\Assert;
use Pharaonic\Readable\Support\Intl;

/**
 * Readable numbers.
 *
 * Without a $locale every method uses a built-in English formatter ("," thousands, "." decimals)
 * that gives the same output on every machine. With a $locale, ext-intl is used.
 */
final class Number
{
    private const array COMPACT_UNITS = ['', 'K', 'M', 'B', 'T'];

    private const array ONES = [
        'zero', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten',
        'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen',
    ];

    private const array TENS = [2 => 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'];

    private const array SCALES = [
        '', 'thousand', 'million', 'billion', 'trillion', 'quadrillion', 'quintillion',
        'sextillion', 'septillion', 'octillion', 'nonillion', 'decillion',
    ];

    /**
     * Format a number with grouped thousands: 1234567.891 → "1,234,568", or "1,234,567.89" with 2 decimals.
     *
     * Rounds half away from zero. With $trimZeros, trailing fraction zeros are dropped
     * ("70.50" → "70.5", "70.00" → "70").
     */
    public static function format(
        int|float $number,
        int $decimals = 0,
        bool $trimZeros = false,
        ?string $locale = null
    ): string {
        Assert::finite($number);
        Assert::decimals($decimals);

        $number = self::withoutNegativeZero($number, $decimals);

        if ($locale !== null) {
            $formatter = Intl::formatter($locale, 'DECIMAL');
            Intl::fractionDigits($formatter, $decimals, $trimZeros);

            return Intl::result($formatter->format($number), $formatter);
        }

        $formatted = is_int($number)
            ? self::groupInteger($number, $decimals)
            : number_format($number, $decimals, '.', ',');

        return $trimZeros && $decimals > 0 ? rtrim(rtrim($formatted, '0'), '.') : $formatted;
    }

    /**
     * Shorten a number with a K, M, B or T suffix: 1250000 → "1.3M", 999950 → "1M".
     *
     * Rounds half away from zero to at most $decimals fraction digits and drops trailing zeros.
     * English suffixes only; values past 999T keep the T suffix ("1,500T").
     */
    public static function compact(int|float $number, int $decimals = 1): string
    {
        Assert::finite($number);
        Assert::decimals($decimals);

        $absolute = abs($number);
        $unit = 0;

        while ($unit < count(self::COMPACT_UNITS) - 1 && $absolute >= 1000 ** ($unit + 1)) {
            $unit++;
        }

        $value = round($absolute / 1000 ** $unit, $decimals);

        // Rounding can reach the next unit: 999 950 is "1M", not "1,000K".
        if ($value >= 1000 && $unit < count(self::COMPACT_UNITS) - 1) {
            $value = round($absolute / 1000 ** ++$unit, $decimals);
        }

        $sign = $number < 0 && $value > 0 ? '-' : '';

        return $sign . self::format($value, $decimals, true) . self::COMPACT_UNITS[$unit];
    }

    /**
     * Format a ratio as a percentage: 0.75 → "75%", 0.1234 with 1 decimal → "12.3%".
     *
     * The input is a fraction of 1 (like Intl's PERCENT style), so 1 is "100%".
     */
    public static function percentage(int|float $ratio, int $decimals = 0, ?string $locale = null): string
    {
        Assert::finite($ratio);
        Assert::decimals($decimals);

        if ($locale !== null) {
            $formatter = Intl::formatter($locale, 'PERCENT');
            Intl::fractionDigits($formatter, $decimals);

            return Intl::result($formatter->format(self::withoutNegativeZero($ratio, $decimals + 2)), $formatter);
        }

        $percent = $ratio * 100;

        // 0.285 * 100 is 28.499999999999996. Cut that binary noise (15 significant digits) so half-up
        // rounding sees 28.5, as Intl does, instead of relying on round()'s internal pre-rounding.
        if (is_float($percent)) {
            $percent = (float) str_replace(',', '.', sprintf('%.15G', $percent));
        }

        return self::format($percent, $decimals) . '%';
    }

    /**
     * Format an integer as an ordinal: 1 → "1st", 22 → "22nd", 113 → "113th", 1001 → "1,001st".
     */
    public static function ordinal(int $number, ?string $locale = null): string
    {
        if ($locale !== null) {
            $formatter = Intl::formatter($locale, 'ORDINAL');

            return Intl::result($formatter->format($number), $formatter);
        }

        $lastTwo = abs($number % 100);

        if ($lastTwo >= 11 && $lastTwo <= 13) {
            $suffix = 'th';
        } else {
            $suffix = [1 => 'st', 2 => 'nd', 3 => 'rd'][$lastTwo % 10] ?? 'th';
        }

        return self::format($number) . $suffix;
    }

    /**
     * Spell a number out in words: 7721 → "seven thousand seven hundred twenty-one", -1.5 → "minus one point five".
     *
     * The built-in speller follows ICU's English rules (no "and", hyphenated tens, fraction digits read one by one).
     * Floats use their shortest exact representation, so 0.1 is "zero point one".
     */
    public static function spell(int|float $number, ?string $locale = null): string
    {
        Assert::finite($number);

        if ($locale !== null) {
            $formatter = Intl::formatter($locale, 'SPELLOUT');
            $words = Intl::result($formatter->format($number), $formatter);

            // In Arabic the conjunction "و" attaches to the following word.
            return preg_match('/^ar(?:[_-]|$)/i', $locale) === 1 ? str_replace('و ', 'و', $words) : $words;
        }

        [$negative, $integer, $fraction] = self::decimalParts($number);

        if (strlen($integer) > 3 * count(self::SCALES)) {
            throw new InvalidArgumentException('The number is too large to spell out.');
        }

        $words = self::spellInteger($integer);

        if ($fraction !== '') {
            $digits = array_map(static fn (string $digit): string => self::ONES[(int) $digit], str_split($fraction));
            $words .= ' point ' . implode(' ', $digits);
        }

        return ($negative && $words !== 'zero' ? 'minus ' : '') . $words;
    }

    /**
     * Group an integer's digits without going through float: number_format() converts ints to float,
     * which loses precision past 2^53.
     */
    private static function groupInteger(int $number, int $decimals): string
    {
        $digits = ltrim((string) $number, '-');
        $grouped = strrev(implode(',', str_split(strrev($digits), 3)));

        return ($number < 0 ? '-' : '') . $grouped . ($decimals > 0 ? '.' . str_repeat('0', $decimals) : '');
    }

    /**
     * Turn a value that rounds to zero into a plain zero, so it never prints as "-0".
     */
    private static function withoutNegativeZero(int|float $number, int $decimals): int|float
    {
        return $number < 0 && round($number, $decimals) == 0 ? 0 : $number;
    }

    /**
     * Split a number into its sign, integer digits and fraction digits without exponent notation.
     *
     * @return array{bool, string, string}
     */
    private static function decimalParts(int|float $number): array
    {
        if (is_int($number)) {
            return [$number < 0, ltrim((string) $number, '-'), ''];
        }

        // Shortest scientific form that converts back to the same float, e.g. "1.5E+3".
        for ($precision = 0; $precision < 17; $precision++) {
            $scientific = str_replace(',', '.', sprintf('%.' . $precision . 'E', $number));

            if ((float) $scientific === $number) {
                break;
            }
        }

        [$mantissa, $exponent] = explode('E', $scientific);
        $negative = $mantissa[0] === '-';
        $digits = str_replace(['-', '.'], '', $mantissa);
        $point = 1 + (int) $exponent;

        if ($point <= 0) {
            $integer = '0';
            $fraction = str_repeat('0', -$point) . $digits;
        } elseif ($point >= strlen($digits)) {
            $integer = $digits . str_repeat('0', $point - strlen($digits));
            $fraction = '';
        } else {
            $integer = substr($digits, 0, $point);
            $fraction = substr($digits, $point);
        }

        return [$negative, ltrim($integer, '0') ?: '0', rtrim($fraction, '0')];
    }

    private static function spellInteger(string $digits): string
    {
        if ($digits === '0') {
            return self::ONES[0];
        }

        $groups = array_reverse(str_split(str_pad($digits, (int) ceil(strlen($digits) / 3) * 3, '0', STR_PAD_LEFT), 3));
        $words = [];

        foreach ($groups as $scale => $group) {
            if ((int) $group === 0) {
                continue;
            }

            array_unshift($words, trim(self::spellHundreds((int) $group) . ' ' . self::SCALES[$scale]));
        }

        return implode(' ', $words);
    }

    private static function spellHundreds(int $number): string
    {
        $words = [];

        if ($number >= 100) {
            $words[] = self::ONES[intdiv($number, 100)] . ' hundred';
            $number %= 100;
        }

        if ($number >= 20) {
            $words[] = self::TENS[intdiv($number, 10)] . ($number % 10 ? '-' . self::ONES[$number % 10] : '');
        } elseif ($number > 0) {
            $words[] = self::ONES[$number];
        }

        return implode(' ', $words);
    }
}
