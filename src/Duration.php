<?php

declare(strict_types=1);

namespace Pharaonic\Readable;

use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use InvalidArgumentException;

/**
 * Readable durations in English: "1 hour 1 minute", or "1h 1m" in short form.
 */
final class Duration
{
    /**
     * Unit => [singular, plural, short].
     */
    private const NAMES = [
        'year' => ['year', 'years', 'y'],
        'month' => ['month', 'months', 'mo'],
        'week' => ['week', 'weeks', 'w'],
        'day' => ['day', 'days', 'd'],
        'hour' => ['hour', 'hours', 'h'],
        'minute' => ['minute', 'minutes', 'm'],
        'second' => ['second', 'seconds', 's'],
    ];

    /**
     * Fixed-length units used for a bare number of seconds. Months are left out because they have no fixed length.
     */
    private const SECONDS = [
        'year' => 31536000, // 365 days
        'week' => 604800,
        'day' => 86400,
        'hour' => 3600,
        'minute' => 60,
        'second' => 1,
    ];

    /**
     * Format a number of seconds: 3661 → "1 hour 1 minute 1 second", 90061 with $parts = 2 → "1 day 1 hour".
     *
     * A year is 365 days. $parts keeps only the largest non-zero units (truncated, not rounded).
     * The sign is ignored: -60 is "1 minute". Zero is "0 seconds".
     */
    public static function format(
        int $seconds,
        ?int $parts = null,
        bool $short = false,
        string $separator = ' '
    ): string {
        $values = [];

        foreach (self::SECONDS as $unit => $length) {
            // intdiv() and % keep the sign, so PHP_INT_MIN never needs abs().
            $values[$unit] = abs(intdiv($seconds, $length));
            $seconds %= $length;
        }

        return self::render($values, $parts, $short, $separator);
    }

    /**
     * Format the calendar difference between two moments: ('2024-01-01', '2025-03-15') → "1 year 2 months 2 weeks".
     *
     * Accepts DateTimeInterface objects, Unix timestamps and strings understood by DateTimeImmutable.
     * Order does not matter. Uses native DateTimeInterface::diff(), so months and years follow the calendar,
     * whole days follow the wall clock across DST changes, and hours count the time that actually elapsed.
     *
     * @throws InvalidArgumentException When a string is not a valid date.
     */
    public static function between(
        DateTimeInterface|int|string $start,
        DateTimeInterface|int|string $end,
        ?int $parts = null,
        bool $short = false,
        string $separator = ' '
    ): string {
        $interval = self::date($start)->diff(self::date($end));

        return self::render([
            'year' => $interval->y,
            'month' => $interval->m,
            'week' => intdiv($interval->d, 7),
            'day' => $interval->d % 7,
            'hour' => $interval->h,
            'minute' => $interval->i,
            'second' => $interval->s,
        ], $parts, $short, $separator);
    }

    /**
     * @param array<string, int> $values
     */
    private static function render(array $values, ?int $parts, bool $short, string $separator): string
    {
        if ($parts !== null && $parts < 1) {
            throw new InvalidArgumentException(sprintf('Parts must be 1 or greater, %d given.', $parts));
        }

        $segments = [];

        foreach ($values as $unit => $value) {
            if ($value === 0) {
                continue;
            }

            $segments[] = self::segment($value, $unit, $short);

            if (count($segments) === $parts) {
                break;
            }
        }

        return $segments === [] ? self::segment(0, 'second', $short) : implode($separator, $segments);
    }

    private static function segment(int $value, string $unit, bool $short): string
    {
        [$singular, $plural, $abbreviation] = self::NAMES[$unit];

        if ($short) {
            return $value . $abbreviation;
        }

        return $value . ' ' . ($value === 1 ? $singular : $plural);
    }

    private static function date(DateTimeInterface|int|string $value): DateTimeInterface
    {
        if ($value instanceof DateTimeInterface) {
            return $value;
        }

        if (is_int($value)) {
            return new DateTimeImmutable('@' . $value);
        }

        try {
            return new DateTimeImmutable($value);
        } catch (Exception $exception) {
            throw new InvalidArgumentException(sprintf('"%s" is not a valid date.', $value), 0, $exception);
        }
    }
}
