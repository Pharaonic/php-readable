<?php

declare(strict_types=1);

use Pharaonic\Readable\Bytes;
use Pharaonic\Readable\Exceptions\MissingIntlExtension;

it('formats bytes with decimal units', function (int|float $bytes, string $expected) {
    expect(Bytes::format($bytes))->toBe($expected);
})->with([
    'zero' => [0, '0 B'],
    'one' => [1, '1 B'],
    'below kilo' => [999, '999 B'],
    'kilo' => [1000, '1 KB'],
    'binary kilo is not 1 KB' => [1024, '1.02 KB'],
    'fraction' => [1500, '1.5 KB'],
    'carries after rounding' => [999999, '1 MB'],
    'stays below carry' => [999994, '999.99 KB'],
    'mega' => [1048576, '1.05 MB'],
    'giga' => [1500000000, '1.5 GB'],
    'tera' => [2000000000000, '2 TB'],
    'peta' => [1e15, '1 PB'],
    'int max' => [PHP_INT_MAX, '9.22 EB'],
    'zetta' => [1e21, '1 ZB'],
    'yotta' => [1e24, '1 YB'],
    'beyond yotta' => [1e27, '1,000 YB'],
    'negative' => [-1500, '-1.5 KB'],
    'negative zero' => [-0.001, '0 B'],
    'fractional bytes' => [0.5, '0.5 B'],
]);

it('formats bytes with binary units', function (int|float $bytes, string $expected) {
    expect(Bytes::format($bytes, 2, true))->toBe($expected);
})->with([
    'zero' => [0, '0 B'],
    'below kibi' => [1023, '1,023 B'],
    'kibi' => [1024, '1 KiB'],
    'whole kibibytes' => [1024 * 7, '7 KiB'],
    'fraction' => [1536, '1.5 KiB'],
    'carries after rounding' => [1048575, '1 MiB'],
    'mebi' => [1048576, '1 MiB'],
    'gibi' => [1073741824, '1 GiB'],
    'tebi' => [1099511627776, '1 TiB'],
    'int max' => [PHP_INT_MAX, '8 EiB'],
    'negative' => [-2048, '-2 KiB'],
]);

it('formats bytes with the given decimals', function () {
    expect(Bytes::format(1234, 1))->toBe('1.2 KB')
        ->and(Bytes::format(1234, 0))->toBe('1 KB')
        ->and(Bytes::format(1234, 3))->toBe('1.234 KB')
        // Half rounds away from zero.
        ->and(Bytes::format(1500, 0))->toBe('2 KB');
});

it('rejects negative decimals', function () {
    Bytes::format(1, -1);
})->throws(InvalidArgumentException::class);

it('rejects a non-finite size', function () {
    Bytes::format(INF);
})->throws(InvalidArgumentException::class);

it('formats bytes with a locale', function () {
    expect(Bytes::format(1500, 2, false, 'de_DE'))->toBe('1,5 KB')
        ->and(Bytes::format(1536, 2, true, 'en_US'))->toBe('1.5 KiB');
})->skip(withoutIntl(), 'ext-intl is not installed.');

it('needs ext-intl to format bytes with a locale', function () {
    Bytes::format(1500, 2, false, 'de_DE');
})->skip(! withoutIntl(), 'ext-intl is installed.')->throws(MissingIntlExtension::class);

it('parses a size into bytes', function (string $size, int $expected) {
    expect(Bytes::parse($size))->toBe($expected);
})->with([
    'plain number' => ['512', 512],
    'zero' => ['0', 0],
    'bytes symbol' => ['512 B', 512],
    'bytes word' => ['512 bytes', 512],
    'byte word' => ['1 byte', 1],
    'kilo' => ['1 KB', 1000],
    'mega' => ['10 MB', 10000000],
    'no space' => ['10MB', 10000000],
    'lowercase' => ['10 mb', 10000000],
    'surrounding whitespace' => ["  10 MB\n", 10000000],
    'kibi' => ['1 KiB', 1024],
    'kibi lowercase' => ['1.5kib', 1536],
    'gibi' => ['2 GiB', 2147483648],
    'fraction' => ['1.5 GB', 1500000000],
    'leading dot' => ['.5 KB', 500],
    'trailing dot' => ['5. KB', 5000],
    'rounds fractional bytes' => ['1.5 B', 2],
    'negative' => ['-1.5 KB', -1500],
    'plus sign' => ['+2 KB', 2000],
    'exa binary' => ['7 EiB', 8070450532247928832],
    'round trip' => [Bytes::format(1536, 2, true), 1536],
    'exact above 2^53' => ['9007199254740993', 9007199254740993],
    'int max' => [(string) PHP_INT_MAX, PHP_INT_MAX],
    'int min' => [(string) PHP_INT_MIN, PHP_INT_MIN],
    'leading zeros' => ['007 KB', 7000],
    'negative zero' => ['-0 KB', 0],
    'zero yotta' => ['0 YB', 0],
    'largest exact kibi' => ['9007199254740991 KiB', 9007199254740991 * 1024],
]);

it('rejects a size it cannot parse', function (string $size) {
    Bytes::parse($size);
})->with([
    'empty' => [''],
    'unit only' => ['MB'],
    'prefix without byte' => ['10 K'],
    'unknown unit' => ['10 XB'],
    'bits are not bytes' => ['10 Mbit'],
    'thousands separator' => ['1,000 KB'],
    'two numbers' => ['1 2 KB'],
    'exponent' => ['1e3 B'],
    'overflow' => ['8 EiB'],
    'int overflow' => ['9223372036854775808'],
    'int overflow after multiplier' => ['9223372036854776 KB'],
    'one yotta overflows' => ['1 YB'],
    'huge' => ['1000 YB'],
])->throws(InvalidArgumentException::class);

it('rejects more than 100 decimals', function () {
    Bytes::format(1500, 101);
})->throws(InvalidArgumentException::class, 'Decimals must be between 0 and 100, 101 given.');

it('quotes a short unparsable size whole in the error message', function () {
    expect(thrownBy(fn() => Bytes::parse('10 K'))->getMessage())->toBe('Cannot parse "10 K" as a byte size.');
});

it('cuts a long unparsable size in the error message', function () {
    expect(thrownBy(fn() => Bytes::parse(str_repeat('x', 1000000)))->getMessage())
        ->toBe('Cannot parse "' . str_repeat('x', 50) . '…" as a byte size.');
});

it('cuts a long oversized size in the error message', function () {
    expect(thrownBy(fn() => Bytes::parse(str_repeat('9', 1000)))->getMessage())
        ->toBe('"' . str_repeat('9', 50) . '…" is too large to be represented in bytes.');
});

it('never cuts a multibyte character in the error message', function () {
    $message = thrownBy(fn() => Bytes::parse('x' . str_repeat('é', 100)))->getMessage();

    expect(mb_check_encoding($message, 'UTF-8'))->toBeTrue()
        ->and($message)->toBe('Cannot parse "x' . str_repeat('é', 24) . '…" as a byte size.');
});
