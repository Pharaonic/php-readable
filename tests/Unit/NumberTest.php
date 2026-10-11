<?php

declare(strict_types=1);

use Pharaonic\Readable\Exceptions\MissingIntlExtension;
use Pharaonic\Readable\Number;

it('formats a number', function (int|float $number, int $decimals, bool $trimZeros, string $expected) {
    expect(Number::format($number, $decimals, $trimZeros))->toBe($expected);
})->with([
    'integer' => [1234567, 0, false, '1,234,567'],
    'zero' => [0, 0, false, '0'],
    'zero with decimals' => [0, 2, false, '0.00'],
    'negative' => [-1234567, 0, false, '-1,234,567'],
    'small' => [999, 0, false, '999'],
    'float rounded' => [1234.5, 0, false, '1,235'],
    'negative half rounds away from zero' => [-2.5, 0, false, '-3'],
    'two decimals' => [60708.547, 2, false, '60,708.55'],
    'integer padded' => [70, 2, false, '70.00'],
    'trim zeros on integral float' => [70.0, 2, true, '70'],
    'trim one zero' => [70.5, 2, true, '70.5'],
    'trim nothing' => [70.07, 2, true, '70.07'],
    'trim keeps integer zeros' => [1000, 2, true, '1,000'],
    'negative zero is unsigned' => [-0.001, 2, false, '0.00'],
    'negative zero without decimals' => [-0.4, 0, false, '0'],
    'int max' => [PHP_INT_MAX, 0, false, '9,223,372,036,854,775,807'],
    'int min' => [PHP_INT_MIN, 0, false, '-9,223,372,036,854,775,808'],
    'large float' => [1.5e15, 0, false, '1,500,000,000,000,000'],
]);

it('formats a number without decimals by default', function () {
    expect(Number::format(1234567.891))->toBe('1,234,568');
});

it('rejects a non-finite number', function (float $number) {
    Number::format($number);
})->with([
    'INF' => [INF],
    '-INF' => [-INF],
    'NAN' => [NAN],
])->throws(InvalidArgumentException::class);

it('rejects negative decimals', function () {
    Number::format(1, -1);
})->throws(InvalidArgumentException::class);

it('formats a number with a locale', function () {
    expect(Number::format(1234567.891, 2, false, 'en_US'))->toBe('1,234,567.89')
        ->and(Number::format(1234567.891, 2, false, 'de_DE'))->toBe('1.234.567,89')
        ->and(Number::format(1234.5, 2, true, 'de_DE'))->toBe('1.234,5')
        // Intl rounds half away from zero too.
        ->and(Number::format(-2.5, 0, false, 'en_US'))->toBe('-3')
        ->and(Number::format(-0.001, 2, false, 'en_US'))->toBe('0.00');
})->skip(withoutIntl(), 'ext-intl is not installed.');

it('matches the built-in formatter for English', function () {
    foreach ([0, 1, -1, 999, 1000, 1234567.891, -0.5, PHP_INT_MAX] as $number) {
        expect(Number::format($number, 2, false, 'en'))->toBe(Number::format($number, 2));
    }
})->skip(withoutIntl(), 'ext-intl is not installed.');

it('never leaks an Intl error for a locale Intl rejects', function () {
    // Older PHP/ICU builds fall back to the root locale; newer ones reject unknown locales.
    // Either way, no ValueError or IntlException leaks out.
    try {
        expect(Number::format(1234.5, 1, false, 'not a locale!!'))->not->toBe('');
    } catch (InvalidArgumentException $exception) {
        expect($exception->getMessage())->toContain('not a valid locale');
    }
})->skip(withoutIntl(), 'ext-intl is not installed.');

it('needs ext-intl to format a number with a locale', function () {
    Number::format(1, 0, false, 'de_DE');
})->skip(! withoutIntl(), 'ext-intl is installed.')->throws(MissingIntlExtension::class);

it('formats a compact number', function (int|float $number, int $decimals, string $expected) {
    expect(Number::compact($number, $decimals))->toBe($expected);
})->with([
    'zero' => [0, 1, '0'],
    'below thousand' => [999, 1, '999'],
    'float below thousand' => [12.345, 1, '12.3'],
    'thousand' => [1000, 1, '1K'],
    'thousands with decimal' => [77700, 1, '77.7K'],
    'no decimals' => [77700, 0, '78K'],
    'two decimals' => [77370, 2, '77.37K'],
    'million' => [1200000, 1, '1.2M'],
    'half rounds up' => [1250000, 1, '1.3M'],
    'trailing zero trimmed' => [1000000, 2, '1M'],
    'carries to next unit' => [999950, 1, '1M'],
    'carries without decimals' => [999500, 0, '1M'],
    'stays below carry' => [999949, 1, '999.9K'],
    'billion' => [3400000000, 1, '3.4B'],
    'trillion' => [5000000000000, 1, '5T'],
    'beyond trillion keeps T' => [1.5e15, 1, '1,500T'],
    'negative' => [-1500, 1, '-1.5K'],
    'negative rounds to zero' => [-0.04, 1, '0'],
    'int max' => [PHP_INT_MAX, 1, '9,223,372T'],
    'int min' => [PHP_INT_MIN, 1, '-9,223,372T'],
]);

it('formats a compact number with one decimal by default', function () {
    expect(Number::compact(1234567))->toBe('1.2M');
});

it('rejects a non-finite compact number', function () {
    Number::compact(INF);
})->throws(InvalidArgumentException::class);

it('formats a percentage', function (int|float $ratio, int $decimals, string $expected) {
    expect(Number::percentage($ratio, $decimals))->toBe($expected);
})->with([
    'fraction' => [0.75, 0, '75%'],
    'zero' => [0, 0, '0%'],
    'one' => [1, 0, '100%'],
    'above one' => [2.5, 0, '250%'],
    'negative' => [-0.1, 0, '-10%'],
    'decimals' => [0.1234, 1, '12.3%'],
    'rounding' => [0.12345, 2, '12.35%'],
    'float noise hidden' => [0.07, 0, '7%'],
    'half-up after multiplication noise' => [0.285, 0, '29%'],
    'half-up after multiplication noise with decimals' => [0.14645, 2, '14.65%'],
    'negative zero' => [-0.0001, 0, '0%'],
    'thousands' => [12.5, 0, '1,250%'],
]);

it('formats a percentage with a locale', function () {
    expect(Number::percentage(0.75, 0, 'en_US'))->toBe('75%')
        ->and(Number::percentage(0.1234, 1, 'en_US'))->toBe('12.3%')
        ->and(normalizeSpaces(Number::percentage(0.756, 1, 'de_DE')))->toBe('75,6 %')
        ->and(Number::percentage(-0.0001, 0, 'en_US'))->toBe('0%')
        ->and(Number::percentage(0.285, 0, 'en_US'))->toBe(Number::percentage(0.285));
})->skip(withoutIntl(), 'ext-intl is not installed.');

it('needs ext-intl to format a percentage with a locale', function () {
    Number::percentage(0.5, 0, 'en_US');
})->skip(! withoutIntl(), 'ext-intl is installed.')->throws(MissingIntlExtension::class);

it('formats an ordinal', function (int $number, string $expected) {
    expect(Number::ordinal($number))->toBe($expected);
})->with([
    '0' => [0, '0th'],
    '1' => [1, '1st'],
    '2' => [2, '2nd'],
    '3' => [3, '3rd'],
    '4' => [4, '4th'],
    '11' => [11, '11th'],
    '12' => [12, '12th'],
    '13' => [13, '13th'],
    '21' => [21, '21st'],
    '22' => [22, '22nd'],
    '23' => [23, '23rd'],
    '101' => [101, '101st'],
    '111' => [111, '111th'],
    '112' => [112, '112th'],
    '1001' => [1001, '1,001st'],
    '-1' => [-1, '-1st'],
    '-11' => [-11, '-11th'],
    '-22' => [-22, '-22nd'],
    'int max' => [PHP_INT_MAX, '9,223,372,036,854,775,807th'],
    'int min' => [PHP_INT_MIN, '-9,223,372,036,854,775,808th'],
]);

it('formats an ordinal with a locale', function () {
    expect(Number::ordinal(21, 'en_US'))->toBe('21st')
        ->and(Number::ordinal(21, 'de_DE'))->toBe('21.')
        ->and(Number::ordinal(21, 'fr_FR'))->toBe('21e');
})->skip(withoutIntl(), 'ext-intl is not installed.');

it('needs ext-intl to format an ordinal with a locale', function () {
    Number::ordinal(1, 'en_US');
})->skip(! withoutIntl(), 'ext-intl is installed.')->throws(MissingIntlExtension::class);

it('spells a number', function (int|float $number, string $expected) {
    expect(Number::spell($number))->toBe($expected);
})->with(function () {
    $intMax = 'nine quintillion two hundred twenty-three quadrillion three hundred seventy-two trillion'
        . ' thirty-six billion eight hundred fifty-four million seven hundred seventy-five thousand eight hundred';

    return [
        'zero' => [0, 'zero'],
        'one' => [1, 'one'],
        'teen' => [13, 'thirteen'],
        'twenty' => [20, 'twenty'],
        'hyphenated' => [21, 'twenty-one'],
        'hundred' => [100, 'one hundred'],
        'no and' => [101, 'one hundred one'],
        'thousands' => [7721, 'seven thousand seven hundred twenty-one'],
        'skips empty groups' => [1000001, 'one million one'],
        'negative' => [-5, 'minus five'],
        'int max' => [PHP_INT_MAX, $intMax . ' seven'],
        'int min' => [PHP_INT_MIN, 'minus ' . $intMax . ' eight'],
        'integral float' => [3.0, 'three'],
        'float' => [1.5, 'one point five'],
        'fraction digits one by one' => [3.14, 'three point one four'],
        'below one' => [0.1, 'zero point one'],
        'negative float' => [-0.25, 'minus zero point two five'],
        'negative zero' => [-0.0, 'zero'],
        'shortest float representation' => [0.1 + 0.2, 'zero point three' . str_repeat(' zero', 15) . ' four'],
        'small exponent' => [2.5e-5, 'zero point zero zero zero zero two five'],
        'large exponent' => [1e20, 'one hundred quintillion'],
        'decillion' => [1e33, 'one decillion'],
    ];
});

it('rejects a number too large to spell', function () {
    Number::spell(1e36);
})->throws(InvalidArgumentException::class);

it('rejects a non-finite number to spell', function () {
    Number::spell(NAN);
})->throws(InvalidArgumentException::class);

it('spells English exactly like ICU', function () {
    $icu = new NumberFormatter('en', NumberFormatter::SPELLOUT);
    $numbers = array_merge(
        range(-30, 130),
        [999, 1000, 1001, 1010, 7721, 100000, 1000001, 123456789, 1.5, 0.1, -0.25, 3.14, 2.5e-5],
    );

    foreach ($numbers as $number) {
        expect(Number::spell($number))->toBe($icu->format($number));
    }
})->skip(withoutIntl(), 'ext-intl is not installed.');

it('spells a number with a locale', function () {
    expect(Number::spell(21, 'en'))->toBe('twenty-one')
        ->and(Number::spell(21, 'fr'))->toBe('vingt-et-un')
        // Arabic "و" attaches to the next word.
        ->and(Number::spell(21, 'ar'))->not->toContain('و ')
        ->and(Number::spell(21, 'ar_EG'))->toContain('وعشرون');
})->skip(withoutIntl(), 'ext-intl is not installed.');

it('needs ext-intl to spell a number with a locale', function () {
    Number::spell(1, 'en');
})->skip(! withoutIntl(), 'ext-intl is installed.')->throws(MissingIntlExtension::class);

it('accepts up to 100 decimals', function () {
    expect(Number::format(1.5, 100))->toBe('1.5' . str_repeat('0', 99))
        ->and(Number::percentage(0.5, 100))->toBe('50.' . str_repeat('0', 100) . '%')
        ->and(Number::compact(1500, 100))->toBe('1.5K');
});

it('rejects more than 100 decimals', function (string $method, array $arguments) {
    Number::$method(...$arguments);
})->with([
    'format' => ['format', [1.5, 101]],
    'compact' => ['compact', [1.5, 101]],
    'percentage' => ['percentage', [0.5, 101]],
    'int max' => ['format', [1.5, PHP_INT_MAX]],
])->throws(InvalidArgumentException::class, 'Decimals must be between 0 and 100');

it('rejects more than 100 decimals with a locale', function () {
    Number::format(1.5, 101, false, 'en_US');
})->skip(withoutIntl(), 'ext-intl is not installed.')->throws(InvalidArgumentException::class);

it('cuts a long invalid locale in the error message', function () {
    $exception = thrownBy(fn () => Number::format(1, 0, false, str_repeat('x', 10000)));

    // Older ICU builds accept any locale and fall back to the root locale.
    if ($exception instanceof LogicException && $exception->getMessage() === 'Nothing was thrown.') {
        expect(true)->toBeTrue();

        return;
    }

    expect($exception->getMessage())->toBe('"' . str_repeat('x', 50) . '…" is not a valid locale.');
})->skip(withoutIntl(), 'ext-intl is not installed.');

it('cuts a long locale in the missing intl message', function () {
    expect(thrownBy(fn () => Number::format(1, 0, false, str_repeat('x', 10000)))->getMessage())
        ->toStartWith('Formatting for locale "' . str_repeat('x', 50) . '…" requires the intl extension.');
})->skip(! withoutIntl(), 'ext-intl is installed.');
