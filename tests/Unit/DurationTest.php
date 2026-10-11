<?php

declare(strict_types=1);

use Pharaonic\Readable\Duration;

it('formats seconds as a duration', function (int $seconds, string $expected) {
    expect(Duration::format($seconds))->toBe($expected);
})->with([
    'zero' => [0, '0 seconds'],
    'one second' => [1, '1 second'],
    'seconds' => [59, '59 seconds'],
    'one minute' => [60, '1 minute'],
    'hour' => [3600, '1 hour'],
    'hour and minute' => [3660, '1 hour 1 minute'],
    'all small units' => [3661, '1 hour 1 minute 1 second'],
    'days and hours' => [187200, '2 days 4 hours'],
    'week' => [604800, '1 week'],
    'weeks not months' => [7777777, '12 weeks 6 days 29 minutes 37 seconds'],
    'year is 365 days' => [31536000, '1 year'],
    '364 days' => [31449600, '52 weeks'],
    'negative ignores sign' => [-3661, '1 hour 1 minute 1 second'],
    'int max' => [PHP_INT_MAX, '292471208677 years 27 weeks 6 days 15 hours 30 minutes 7 seconds'],
    'int min' => [PHP_INT_MIN, '292471208677 years 27 weeks 6 days 15 hours 30 minutes 8 seconds'],
]);

it('limits the number of parts', function () {
    expect(Duration::format(90061, 1))->toBe('1 day')
        ->and(Duration::format(90061, 2))->toBe('1 day 1 hour')
        // Parts truncate, they do not round.
        ->and(Duration::format(7199, 2))->toBe('1 hour 59 minutes')
        // Zero units are skipped, not counted.
        ->and(Duration::format(3601, 2))->toBe('1 hour 1 second')
        ->and(Duration::format(3600, 5))->toBe('1 hour');
});

it('formats short units', function () {
    expect(Duration::format(3661, null, true))->toBe('1h 1m 1s')
        ->and(Duration::format(31536000 + 604800 + 86400, null, true))->toBe('1y 1w 1d')
        ->and(Duration::format(0, null, true))->toBe('0s');
});

it('joins parts with the given separator', function () {
    expect(Duration::format(3661, null, false, ', '))->toBe('1 hour, 1 minute, 1 second')
        ->and(Duration::format(3660, null, true, ':'))->toBe('1h:1m');
});

it('rejects zero parts', function () {
    Duration::format(60, 0);
})->throws(InvalidArgumentException::class);

it('formats the duration between two date strings', function () {
    expect(Duration::between('2024-01-01', '2025-03-15'))->toBe('1 year 2 months 2 weeks')
        ->and(Duration::between('1993-02-01 19:00:00', '2022-02-01 19:00:00'))->toBe('29 years');
});

it('ignores the order of the two moments', function () {
    expect(Duration::between('2025-03-15', '2024-01-01'))->toBe(Duration::between('2024-01-01', '2025-03-15'));
});

it('formats the same moment as zero seconds', function () {
    expect(Duration::between('2024-01-01', '2024-01-01'))->toBe('0 seconds');
});

it('formats the duration between two timestamps', function () {
    expect(Duration::between(0, 3661))->toBe('1 hour 1 minute 1 second');
});

it('uses calendar months between dates', function () {
    // February 2024 has 29 days, April has 30.
    expect(Duration::between('2024-02-01', '2024-03-01'))->toBe('1 month')
        ->and(Duration::between('2024-04-01', '2024-05-01'))->toBe('1 month')
        // Seconds never become months.
        ->and(Duration::format(29 * 86400))->toBe('4 weeks 1 day');
});

it('formats the duration between date objects', function () {
    $zone = new DateTimeZone('Europe/Helsinki');

    expect(Duration::between(new DateTimeImmutable('2024-01-01', $zone), new DateTime('2025-03-15', $zone)))
        ->toBe('1 year 2 months 2 weeks');
});

it('keeps the wall clock across daylight saving', function () {
    $zone = new DateTimeZone('Europe/Helsinki');

    expect(Duration::between(
        new DateTimeImmutable('2021-03-27 12:00', $zone),
        new DateTimeImmutable('2021-03-28 12:00', $zone),
    ))->toBe('1 day');
});

it('counts elapsed hours across daylight saving', function () {
    $zone = new DateTimeZone('Europe/Helsinki');

    expect(Duration::between(
        new DateTimeImmutable('2021-03-28 00:00', $zone),
        new DateTimeImmutable('2021-03-28 06:00', $zone),
    ))->toBe('5 hours');
});

it('compares instants across time zones', function () {
    expect(Duration::between(
        new DateTimeImmutable('2024-01-01 12:00', new DateTimeZone('UTC')),
        new DateTimeImmutable('2024-01-01 14:00', new DateTimeZone('Africa/Cairo')),
    ))->toBe('0 seconds');
});

it('passes the format options through', function () {
    expect(Duration::between('2024-01-01', '2025-03-15', 2, true))->toBe('1y 2mo')
        ->and(Duration::between('2024-01-01', '2025-03-15', null, false, ', '))->toBe('1 year, 2 months, 2 weeks');
});

it('rejects an invalid date', function () {
    Duration::between('not a date', 'now');
})->throws(InvalidArgumentException::class);
