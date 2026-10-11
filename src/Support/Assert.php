<?php

declare(strict_types=1);

namespace Pharaonic\Readable\Support;

use InvalidArgumentException;

/**
 * @internal
 */
final class Assert
{
    /**
     * Far beyond any display need. Larger values exhaust memory in number_format() or make Intl return wrong digits.
     */
    private const int MAX_DECIMALS = 100;

    public static function finite(int|float $number): void
    {
        if (is_float($number) && !is_finite($number)) {
            throw new InvalidArgumentException('Cannot format a non-finite number (INF or NAN).');
        }
    }

    public static function decimals(int $decimals): void
    {
        if ($decimals < 0 || $decimals > self::MAX_DECIMALS) {
            throw new InvalidArgumentException(
                sprintf('Decimals must be between 0 and %d, %d given.', self::MAX_DECIMALS, $decimals),
            );
        }
    }
}
