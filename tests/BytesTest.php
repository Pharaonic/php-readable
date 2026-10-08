<?php

declare(strict_types=1);

namespace Pharaonic\Readable\Tests;

use InvalidArgumentException;
use Pharaonic\Readable\Bytes;

final class BytesTest extends IntlTestCase
{
    /**
     * @return iterable<string, array{int|float, string}>
     */
    public static function decimalProvider(): iterable
    {
        yield 'zero' => [0, '0 B'];
        yield 'one' => [1, '1 B'];
        yield 'below kilo' => [999, '999 B'];
        yield 'kilo' => [1000, '1 KB'];
        yield 'binary kilo is not 1 KB' => [1024, '1.02 KB'];
        yield 'fraction' => [1500, '1.5 KB'];
        yield 'carries after rounding' => [999999, '1 MB'];
        yield 'stays below carry' => [999994, '999.99 KB'];
        yield 'mega' => [1048576, '1.05 MB'];
        yield 'giga' => [1500000000, '1.5 GB'];
        yield 'tera' => [2000000000000, '2 TB'];
        yield 'peta' => [1e15, '1 PB'];
        yield 'int max' => [PHP_INT_MAX, '9.22 EB'];
        yield 'zetta' => [1e21, '1 ZB'];
        yield 'yotta' => [1e24, '1 YB'];
        yield 'beyond yotta' => [1e27, '1,000 YB'];
        yield 'negative' => [-1500, '-1.5 KB'];
        yield 'negative zero' => [-0.001, '0 B'];
        yield 'fractional bytes' => [0.5, '0.5 B'];
    }

    /**
     * @dataProvider decimalProvider
     */
    public function testFormatDecimal(int|float $bytes, string $expected): void
    {
        self::assertSame($expected, Bytes::format($bytes));
    }

    /**
     * @return iterable<string, array{int|float, string}>
     */
    public static function binaryProvider(): iterable
    {
        yield 'zero' => [0, '0 B'];
        yield 'below kibi' => [1023, '1,023 B'];
        yield 'kibi' => [1024, '1 KiB'];
        yield 'whole kibibytes' => [1024 * 7, '7 KiB'];
        yield 'fraction' => [1536, '1.5 KiB'];
        yield 'carries after rounding' => [1048575, '1 MiB'];
        yield 'mebi' => [1048576, '1 MiB'];
        yield 'gibi' => [1073741824, '1 GiB'];
        yield 'tebi' => [1099511627776, '1 TiB'];
        yield 'int max' => [PHP_INT_MAX, '8 EiB'];
        yield 'negative' => [-2048, '-2 KiB'];
    }

    /**
     * @dataProvider binaryProvider
     */
    public function testFormatBinary(int|float $bytes, string $expected): void
    {
        self::assertSame($expected, Bytes::format($bytes, 2, true));
    }

    public function testFormatDecimals(): void
    {
        self::assertSame('1.2 KB', Bytes::format(1234, 1));
        self::assertSame('1 KB', Bytes::format(1234, 0));
        self::assertSame('1.234 KB', Bytes::format(1234, 3));
        self::assertSame('2 KB', Bytes::format(1500, 0), 'Half rounds away from zero');
    }

    public function testFormatRejectsNegativeDecimals(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Bytes::format(1, -1);
    }

    public function testFormatRejectsNonFinite(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Bytes::format(INF);
    }

    public function testFormatWithLocale(): void
    {
        $this->requireIntl();

        self::assertSame('1,5 KB', Bytes::format(1500, 2, false, 'de_DE'));
        self::assertSame('1.5 KiB', Bytes::format(1536, 2, true, 'en_US'));
    }

    public function testFormatWithLocaleRequiresIntl(): void
    {
        $this->expectMissingIntl();

        Bytes::format(1500, 2, false, 'de_DE');
    }

    /**
     * @return iterable<string, array{string, int}>
     */
    public static function parseProvider(): iterable
    {
        yield 'plain number' => ['512', 512];
        yield 'zero' => ['0', 0];
        yield 'bytes symbol' => ['512 B', 512];
        yield 'bytes word' => ['512 bytes', 512];
        yield 'byte word' => ['1 byte', 1];
        yield 'kilo' => ['1 KB', 1000];
        yield 'mega' => ['10 MB', 10000000];
        yield 'no space' => ['10MB', 10000000];
        yield 'lowercase' => ['10 mb', 10000000];
        yield 'surrounding whitespace' => ["  10 MB\n", 10000000];
        yield 'kibi' => ['1 KiB', 1024];
        yield 'kibi lowercase' => ['1.5kib', 1536];
        yield 'gibi' => ['2 GiB', 2147483648];
        yield 'fraction' => ['1.5 GB', 1500000000];
        yield 'leading dot' => ['.5 KB', 500];
        yield 'trailing dot' => ['5. KB', 5000];
        yield 'rounds fractional bytes' => ['1.5 B', 2];
        yield 'negative' => ['-1.5 KB', -1500];
        yield 'plus sign' => ['+2 KB', 2000];
        yield 'exa binary' => ['7 EiB', 8070450532247928832];
        yield 'round trip' => [Bytes::format(1536, 2, true), 1536];
        yield 'exact above 2^53' => ['9007199254740993', 9007199254740993];
        yield 'int max' => [(string) PHP_INT_MAX, PHP_INT_MAX];
        yield 'int min' => [(string) PHP_INT_MIN, PHP_INT_MIN];
        yield 'leading zeros' => ['007 KB', 7000];
        yield 'negative zero' => ['-0 KB', 0];
        yield 'zero yotta' => ['0 YB', 0];
        yield 'largest exact kibi' => ['9007199254740991 KiB', 9007199254740991 * 1024];
    }

    /**
     * @dataProvider parseProvider
     */
    public function testParse(string $size, int $expected): void
    {
        self::assertSame($expected, Bytes::parse($size));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidParseProvider(): iterable
    {
        yield 'empty' => [''];
        yield 'unit only' => ['MB'];
        yield 'prefix without byte' => ['10 K'];
        yield 'unknown unit' => ['10 XB'];
        yield 'bits are not bytes' => ['10 Mbit'];
        yield 'thousands separator' => ['1,000 KB'];
        yield 'two numbers' => ['1 2 KB'];
        yield 'exponent' => ['1e3 B'];
        yield 'overflow' => ['8 EiB'];
        yield 'int overflow' => ['9223372036854775808'];
        yield 'int overflow after multiplier' => ['9223372036854776 KB'];
        yield 'one yotta overflows' => ['1 YB'];
        yield 'huge' => ['1000 YB'];
    }

    /**
     * @dataProvider invalidParseProvider
     */
    public function testParseRejectsInvalidInput(string $size): void
    {
        $this->expectException(InvalidArgumentException::class);

        Bytes::parse($size);
    }
}
