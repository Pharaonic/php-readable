<?php

declare(strict_types=1);

namespace Pharaonic\Readable\Support;

use InvalidArgumentException;

/**
 * @internal
 */
final class Assert
{
    public static function finite(int|float $number): void
    {
        if (is_float($number) && !is_finite($number)) {
            throw new InvalidArgumentException('Cannot format a non-finite number (INF or NAN).');
        }
    }

    public static function decimals(int $decimals): void
    {
        if ($decimals < 0) {
            throw new InvalidArgumentException(sprintf('Decimals must be zero or greater, %d given.', $decimals));
        }
    }
}
