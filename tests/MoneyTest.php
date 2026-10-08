<?php

declare(strict_types=1);

namespace Pharaonic\Readable\Tests;

use InvalidArgumentException;
use Pharaonic\Readable\Money;

final class MoneyTest extends IntlTestCase
{
    /**
     * @return iterable<string, array{int|float, string, string}>
     */
    public static function formatProvider(): iterable
    {
        yield 'integer' => [100, 'USD', 'USD 100.00'];
        yield 'float' => [1234.5, 'USD', 'USD 1,234.50'];
        yield 'zero' => [0, 'EGP', 'EGP 0.00'];
        yield 'negative' => [-1234.5, 'EUR', '-EUR 1,234.50'];
        yield 'rounds to currency digits' => [10.005, 'USD', 'USD 10.01'];
        yield 'negative rounding to zero is unsigned' => [-0.001, 'USD', 'USD 0.00'];
        yield 'zero-decimal currency' => [1234.5, 'JPY', 'JPY 1,235'];
        yield 'three-decimal currency' => [1.5, 'KWD', 'KWD 1.500'];
        yield 'four-decimal currency' => [1, 'CLF', 'CLF 1.0000'];
        yield 'lowercase code' => [1, 'usd', 'USD 1.00'];
        yield 'unknown code defaults to two digits' => [1, 'XYZ', 'XYZ 1.00'];
        yield 'large' => [1e12, 'USD', 'USD 1,000,000,000,000.00'];
    }

    /**
     * @dataProvider formatProvider
     */
    public function testFormat(int|float $amount, string $currency, string $expected): void
    {
        self::assertSame($expected, Money::format($amount, $currency));
    }

    public function testFormatDecimalsOverride(): void
    {
        self::assertSame('USD 1,235', Money::format(1234.5, 'USD', 0));
        self::assertSame('JPY 1,234.50', Money::format(1234.5, 'JPY', 2));
    }

    /**
     * @dataProvider invalidCurrencyProvider
     */
    public function testFormatRejectsInvalidCurrency(string $currency): void
    {
        $this->expectException(InvalidArgumentException::class);

        Money::format(1, $currency);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidCurrencyProvider(): iterable
    {
        yield 'empty' => [''];
        yield 'too short' => ['US'];
        yield 'too long' => ['USDT'];
        yield 'digits' => ['840'];
        yield 'symbol' => ['$'];
    }

    public function testFormatRejectsNegativeDecimals(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Money::format(1, 'USD', -1);
    }

    public function testFormatRejectsNonFinite(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Money::format(NAN, 'USD');
    }

    public function testFormatWithLocale(): void
    {
        $this->requireIntl();

        self::assertSame('$1,234.50', Money::format(1234.5, 'USD', null, 'en_US'));
        self::assertSame('-$1,234.50', Money::format(-1234.5, 'USD', null, 'en_US'));
        self::assertSame('1.234,50 €', self::normalizeSpaces(Money::format(1234.5, 'EUR', null, 'de_DE')));
        self::assertSame('¥1,235', Money::format(1234.5, 'JPY', null, 'en_US'));
        self::assertSame('$1,235', Money::format(1234.5, 'USD', 0, 'en_US'));
        self::assertSame('$1,234.50', Money::format(1234.5, 'usd', null, 'en_US'));
    }

    public function testFormatWithLocaleNeverPrintsNegativeZero(): void
    {
        $this->requireIntl();

        self::assertSame('$0.00', Money::format(-0.001, 'USD', null, 'en_US'));
        self::assertSame('¥0', Money::format(-0.4, 'JPY', null, 'en_US'));
        self::assertSame('$0', Money::format(-0.4, 'USD', 0, 'en_US'));
        self::assertSame('-$0.01', Money::format(-0.005, 'USD', null, 'en_US'));
    }

    public function testFormatWithLocaleRequiresIntl(): void
    {
        $this->expectMissingIntl();

        Money::format(1, 'USD', null, 'en_US');
    }

    /**
     * @return iterable<string, array{int|float, string, int, string}>
     */
    public static function compactProvider(): iterable
    {
        yield 'million' => [1500000, 'USD', 1, 'USD 1.5M'];
        yield 'small' => [999, 'EGP', 1, 'EGP 999'];
        yield 'zero' => [0, 'USD', 1, 'USD 0'];
        yield 'negative' => [-2500, 'EUR', 1, '-EUR 2.5K'];
        yield 'no decimals' => [1500000, 'USD', 0, 'USD 2M'];
        yield 'negative rounding to zero is unsigned' => [-0.01, 'USD', 1, 'USD 0'];
    }

    /**
     * @dataProvider compactProvider
     */
    public function testCompact(int|float $amount, string $currency, int $decimals, string $expected): void
    {
        self::assertSame($expected, Money::compact($amount, $currency, $decimals));
    }

    public function testCompactRejectsInvalidCurrency(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Money::compact(1, 'dollars');
    }

    /**
     * @return iterable<string, array{string, int}>
     */
    public static function fractionDigitsProvider(): iterable
    {
        yield 'USD' => ['USD', 2];
        yield 'EGP' => ['EGP', 2];
        yield 'JPY' => ['JPY', 0];
        yield 'KRW' => ['KRW', 0];
        yield 'KWD' => ['KWD', 3];
        yield 'BHD' => ['BHD', 3];
        yield 'CLF' => ['CLF', 4];
        yield 'lowercase' => ['jpy', 0];
    }

    /**
     * @dataProvider fractionDigitsProvider
     */
    public function testFractionDigits(string $currency, int $expected): void
    {
        self::assertSame($expected, Money::fractionDigits($currency));
    }
}
