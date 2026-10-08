<?php

declare(strict_types=1);

namespace Pharaonic\Readable;

use InvalidArgumentException;
use Pharaonic\Readable\Support\Assert;

/**
 * Readable byte sizes.
 *
 * Two conventions, never mixed: SI decimal units (1 KB = 1000 B, the default) and
 * IEC binary units (1 KiB = 1024 B).
 */
final class Bytes
{
    private const DECIMAL_UNITS = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

    private const BINARY_UNITS = ['B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB'];

    /**
     * Format a byte count: 1500 → "1.5 KB", or with $binary 1536 → "1.5 KiB".
     *
     * Rounds half away from zero to at most $decimals fraction digits, drops trailing zeros and
     * moves to the next unit when rounding reaches it (999 999 → "1 MB"). Negative sizes keep their sign.
     */
    public static function format(
        int|float $bytes,
        int $decimals = 2,
        bool $binary = false,
        ?string $locale = null
    ): string {
        Assert::finite($bytes);
        Assert::decimals($decimals);

        $base = $binary ? 1024 : 1000;
        $units = $binary ? self::BINARY_UNITS : self::DECIMAL_UNITS;
        $last = count($units) - 1;
        $absolute = abs($bytes);
        $unit = 0;

        while ($unit < $last && $absolute >= $base ** ($unit + 1)) {
            $unit++;
        }

        $value = round($absolute / $base ** $unit, $decimals);

        if ($value >= $base && $unit < $last) {
            $value = round($absolute / $base ** ++$unit, $decimals);
        }

        return Number::format($bytes < 0 ? -$value : $value, $decimals, true, $locale) . ' ' . $units[$unit];
    }

    /**
     * Parse a readable size back into bytes: "10 MB" → 10000000, "1.5 KiB" → 1536, "512" → 512.
     *
     * Units are case-insensitive. "KB", "MB", ... are decimal and "KiB", "MiB", ... are binary,
     * matching format(). Fractional bytes are rounded half away from zero.
     *
     * @throws InvalidArgumentException When the value cannot be parsed or does not fit in an int.
     */
    public static function parse(string $size): int
    {
        // <number> [<prefix>[i]B | B | byte | bytes], e.g. "1.5 KiB", "10MB", "512 bytes".
        $pattern = '/^\s*([+-]?(?:\d+(?:\.\d*)?|\.\d+))\s*(?:([kmgtpezy])(i)?b|b|bytes?)?\s*$/i';

        if (preg_match($pattern, $size, $match) !== 1) {
            throw new InvalidArgumentException(sprintf('Cannot parse "%s" as a byte size.', $size));
        }

        $multiplier = 1;

        if (($match[2] ?? '') !== '') {
            $power = (int) strpos('kmgtpezy', strtolower($match[2])) + 1;
            $multiplier = (($match[3] ?? '') !== '' ? 1024 : 1000) ** $power;
        }

        if (strpos($match[1], '.') === false) {
            return self::multiply($match[1], $multiplier, $size);
        }

        $bytes = round((float) $match[1] * $multiplier);

        // 2^63 is the first float above PHP_INT_MAX.
        if ($bytes >= 9.2233720368547758E18 || $bytes < -9.2233720368547758E18) {
            throw new InvalidArgumentException(sprintf('"%s" is too large to be represented in bytes.', $size));
        }

        return (int) $bytes;
    }

    /**
     * Multiply a whole number without going through float, which loses precision past 2^53.
     *
     * @param int|float $multiplier A float when the unit itself exceeds PHP_INT_MAX (ZB, YB, ZiB, YiB).
     */
    private static function multiply(string $number, int|float $multiplier, string $size): int
    {
        $negative = $number[0] === '-';
        $digits = ltrim($number, '+-0');
        $normalized = ($negative && $digits !== '' ? '-' : '') . ($digits === '' ? '0' : $digits);
        $value = (int) $normalized;

        if ((string) $value === $normalized) {
            if ($value === 0) {
                return 0;
            }

            $fits = is_int($multiplier)
                && $value <= intdiv(PHP_INT_MAX, $multiplier)
                && $value >= intdiv(PHP_INT_MIN, $multiplier);

            if ($fits) {
                return $value * $multiplier;
            }
        }

        throw new InvalidArgumentException(sprintf('"%s" is too large to be represented in bytes.', $size));
    }
}
