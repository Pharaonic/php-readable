<?php

declare(strict_types=1);

use Pharaonic\Readable\Exceptions\MissingIntlExtension;
use Pharaonic\Readable\Money;

it('formats money', function (int|float $amount, string $currency, string $expected) {
    expect(Money::format($amount, $currency))->toBe($expected);
})->with([
    'integer' => [100, 'USD', 'USD 100.00'],
    'float' => [1234.5, 'USD', 'USD 1,234.50'],
    'zero' => [0, 'EGP', 'EGP 0.00'],
    'negative' => [-1234.5, 'EUR', '-EUR 1,234.50'],
    'rounds to currency digits' => [10.005, 'USD', 'USD 10.01'],
    'negative rounding to zero is unsigned' => [-0.001, 'USD', 'USD 0.00'],
    'zero-decimal currency' => [1234.5, 'JPY', 'JPY 1,235'],
    'three-decimal currency' => [1.5, 'KWD', 'KWD 1.500'],
    'four-decimal currency' => [1, 'CLF', 'CLF 1.0000'],
    'lowercase code' => [1, 'usd', 'USD 1.00'],
    'unknown code defaults to two digits' => [1, 'XYZ', 'XYZ 1.00'],
    'large' => [1e12, 'USD', 'USD 1,000,000,000,000.00'],
]);

it('overrides the currency decimals', function () {
    expect(Money::format(1234.5, 'USD', 0))->toBe('USD 1,235')
        ->and(Money::format(1234.5, 'JPY', 2))->toBe('JPY 1,234.50');
});

it('rejects an invalid currency code', function (string $currency) {
    Money::format(1, $currency);
})->with([
    'empty' => [''],
    'too short' => ['US'],
    'too long' => ['USDT'],
    'digits' => ['840'],
    'symbol' => ['$'],
])->throws(InvalidArgumentException::class);

it('rejects negative decimals', function () {
    Money::format(1, 'USD', -1);
})->throws(InvalidArgumentException::class);

it('rejects a non-finite amount', function () {
    Money::format(NAN, 'USD');
})->throws(InvalidArgumentException::class);

it('formats money with a locale', function () {
    expect(Money::format(1234.5, 'USD', null, 'en_US'))->toBe('$1,234.50')
        ->and(Money::format(-1234.5, 'USD', null, 'en_US'))->toBe('-$1,234.50')
        ->and(normalizeSpaces(Money::format(1234.5, 'EUR', null, 'de_DE')))->toBe('1.234,50 €')
        ->and(Money::format(1234.5, 'JPY', null, 'en_US'))->toBe('¥1,235')
        ->and(Money::format(1234.5, 'USD', 0, 'en_US'))->toBe('$1,235')
        ->and(Money::format(1234.5, 'usd', null, 'en_US'))->toBe('$1,234.50');
})->skip(withoutIntl(), 'ext-intl is not installed.');

it('never prints negative zero with a locale', function () {
    expect(Money::format(-0.001, 'USD', null, 'en_US'))->toBe('$0.00')
        ->and(Money::format(-0.4, 'JPY', null, 'en_US'))->toBe('¥0')
        ->and(Money::format(-0.4, 'USD', 0, 'en_US'))->toBe('$0')
        ->and(Money::format(-0.005, 'USD', null, 'en_US'))->toBe('-$0.01');
})->skip(withoutIntl(), 'ext-intl is not installed.');

it('needs ext-intl to format money with a locale', function () {
    Money::format(1, 'USD', null, 'en_US');
})->skip(! withoutIntl(), 'ext-intl is installed.')->throws(MissingIntlExtension::class);

it('formats compact money', function (int|float $amount, string $currency, int $decimals, string $expected) {
    expect(Money::compact($amount, $currency, $decimals))->toBe($expected);
})->with([
    'million' => [1500000, 'USD', 1, 'USD 1.5M'],
    'small' => [999, 'EGP', 1, 'EGP 999'],
    'zero' => [0, 'USD', 1, 'USD 0'],
    'negative' => [-2500, 'EUR', 1, '-EUR 2.5K'],
    'no decimals' => [1500000, 'USD', 0, 'USD 2M'],
    'negative rounding to zero is unsigned' => [-0.01, 'USD', 1, 'USD 0'],
]);

it('rejects an invalid currency code in compact money', function () {
    Money::compact(1, 'dollars');
})->throws(InvalidArgumentException::class);

it('returns the ISO 4217 fraction digits of a currency', function (string $currency, int $expected) {
    expect(Money::fractionDigits($currency))->toBe($expected);
})->with([
    'USD' => ['USD', 2],
    'EGP' => ['EGP', 2],
    'JPY' => ['JPY', 0],
    'KRW' => ['KRW', 0],
    'KWD' => ['KWD', 3],
    'BHD' => ['BHD', 3],
    'CLF' => ['CLF', 4],
    'lowercase' => ['jpy', 0],
]);

it('rejects more than 100 decimals', function (string $method, array $arguments) {
    Money::$method(...$arguments);
})->with([
    'format' => ['format', [1, 'USD', 101]],
    'compact' => ['compact', [1, 'USD', 101]],
])->throws(InvalidArgumentException::class, 'Decimals must be between 0 and 100, 101 given.');

it('cuts a long currency code in the error message', function () {
    expect(thrownBy(fn () => Money::format(1, str_repeat('X', 1000)))->getMessage())
        ->toBe('"' . str_repeat('X', 50) . '…" is not a three-letter ISO 4217 currency code.');
});
