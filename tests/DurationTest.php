<?php

declare(strict_types=1);

namespace Pharaonic\Readable\Tests;

use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use Pharaonic\Readable\Duration;
use PHPUnit\Framework\TestCase;

final class DurationTest extends TestCase
{
    /**
     * @return iterable<string, array{int, string}>
     */
    public static function formatProvider(): iterable
    {
        yield 'zero' => [0, '0 seconds'];
        yield 'one second' => [1, '1 second'];
        yield 'seconds' => [59, '59 seconds'];
        yield 'one minute' => [60, '1 minute'];
        yield 'hour' => [3600, '1 hour'];
        yield 'hour and minute' => [3660, '1 hour 1 minute'];
        yield 'all small units' => [3661, '1 hour 1 minute 1 second'];
        yield 'days and hours' => [187200, '2 days 4 hours'];
        yield 'week' => [604800, '1 week'];
        yield 'weeks not months' => [7777777, '12 weeks 6 days 29 minutes 37 seconds'];
        yield 'year is 365 days' => [31536000, '1 year'];
        yield '364 days' => [31449600, '52 weeks'];
        yield 'negative ignores sign' => [-3661, '1 hour 1 minute 1 second'];
        yield 'int max' => [PHP_INT_MAX, '292471208677 years 27 weeks 6 days 15 hours 30 minutes 7 seconds'];
        yield 'int min' => [PHP_INT_MIN, '292471208677 years 27 weeks 6 days 15 hours 30 minutes 8 seconds'];
    }

    /**
     * @dataProvider formatProvider
     */
    public function testFormat(int $seconds, string $expected): void
    {
        self::assertSame($expected, Duration::format($seconds));
    }

    public function testFormatParts(): void
    {
        self::assertSame('1 day', Duration::format(90061, 1));
        self::assertSame('1 day 1 hour', Duration::format(90061, 2));
        self::assertSame('1 hour 59 minutes', Duration::format(7199, 2), 'Parts truncate, they do not round');
        self::assertSame('1 hour 1 second', Duration::format(3601, 2), 'Zero units are skipped, not counted');
        self::assertSame('1 hour', Duration::format(3600, 5));
    }

    public function testFormatShort(): void
    {
        self::assertSame('1h 1m 1s', Duration::format(3661, null, true));
        self::assertSame('1y 1w 1d', Duration::format(31536000 + 604800 + 86400, null, true));
        self::assertSame('0s', Duration::format(0, null, true));
    }

    public function testFormatSeparator(): void
    {
        self::assertSame('1 hour, 1 minute, 1 second', Duration::format(3661, null, false, ', '));
        self::assertSame('1h:1m', Duration::format(3660, null, true, ':'));
    }

    public function testFormatRejectsZeroParts(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Duration::format(60, 0);
    }

    public function testBetweenStrings(): void
    {
        self::assertSame('1 year 2 months 2 weeks', Duration::between('2024-01-01', '2025-03-15'));
        self::assertSame('29 years', Duration::between('1993-02-01 19:00:00', '2022-02-01 19:00:00'));
    }

    public function testBetweenIsOrderIndependent(): void
    {
        self::assertSame(
            Duration::between('2024-01-01', '2025-03-15'),
            Duration::between('2025-03-15', '2024-01-01')
        );
    }

    public function testBetweenSameMoment(): void
    {
        self::assertSame('0 seconds', Duration::between('2024-01-01', '2024-01-01'));
    }

    public function testBetweenTimestamps(): void
    {
        self::assertSame('1 hour 1 minute 1 second', Duration::between(0, 3661));
    }

    public function testBetweenUsesCalendarMonths(): void
    {
        self::assertSame('1 month', Duration::between('2024-02-01', '2024-03-01'), 'February 2024 has 29 days');
        self::assertSame('1 month', Duration::between('2024-04-01', '2024-05-01'), 'April has 30 days');
        self::assertSame('4 weeks 1 day', Duration::format(29 * 86400), 'Seconds never become months');
    }

    public function testBetweenDateTimeObjects(): void
    {
        $zone = new DateTimeZone('Europe/Helsinki');

        self::assertSame(
            '1 year 2 months 2 weeks',
            Duration::between(new DateTimeImmutable('2024-01-01', $zone), new DateTime('2025-03-15', $zone))
        );
    }

    public function testBetweenAcrossDaylightSavingKeepsWallClock(): void
    {
        $zone = new DateTimeZone('Europe/Helsinki');

        self::assertSame(
            '1 day',
            Duration::between(
                new DateTimeImmutable('2021-03-27 12:00', $zone),
                new DateTimeImmutable('2021-03-28 12:00', $zone)
            )
        );
    }

    public function testBetweenCountsHoursOnTheWallClockAcrossDaylightSaving(): void
    {
        $zone = new DateTimeZone('Europe/Helsinki');

        self::assertSame('6 hours', Duration::between(
            new DateTimeImmutable('2021-03-28 00:00', $zone),
            new DateTimeImmutable('2021-03-28 06:00', $zone)
        ));
    }

    public function testBetweenDifferentZonesComparesInstants(): void
    {
        self::assertSame('0 seconds', Duration::between(
            new DateTimeImmutable('2024-01-01 12:00', new DateTimeZone('UTC')),
            new DateTimeImmutable('2024-01-01 14:00', new DateTimeZone('Africa/Cairo'))
        ));
    }

    public function testBetweenOptions(): void
    {
        self::assertSame('1y 2mo', Duration::between('2024-01-01', '2025-03-15', 2, true));
        self::assertSame('1 year, 2 months, 2 weeks', Duration::between('2024-01-01', '2025-03-15', null, false, ', '));
    }

    public function testBetweenRejectsInvalidDate(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Duration::between('not a date', 'now');
    }
}
