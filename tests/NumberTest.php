<?php

declare(strict_types=1);

namespace Pharaonic\Readable\Tests;

use InvalidArgumentException;
use NumberFormatter;
use Pharaonic\Readable\Number;

final class NumberTest extends IntlTestCase
{
    /**
     * @return iterable<string, array{int|float, int, bool, string}>
     */
    public static function formatProvider(): iterable
    {
        yield 'integer' => [1234567, 0, false, '1,234,567'];
        yield 'zero' => [0, 0, false, '0'];
        yield 'zero with decimals' => [0, 2, false, '0.00'];
        yield 'negative' => [-1234567, 0, false, '-1,234,567'];
        yield 'small' => [999, 0, false, '999'];
        yield 'float rounded' => [1234.5, 0, false, '1,235'];
        yield 'negative half rounds away from zero' => [-2.5, 0, false, '-3'];
        yield 'two decimals' => [60708.547, 2, false, '60,708.55'];
        yield 'integer padded' => [70, 2, false, '70.00'];
        yield 'trim zeros on integral float' => [70.0, 2, true, '70'];
        yield 'trim one zero' => [70.5, 2, true, '70.5'];
        yield 'trim nothing' => [70.07, 2, true, '70.07'];
        yield 'trim keeps integer zeros' => [1000, 2, true, '1,000'];
        yield 'negative zero is unsigned' => [-0.001, 2, false, '0.00'];
        yield 'negative zero without decimals' => [-0.4, 0, false, '0'];
        yield 'int max' => [PHP_INT_MAX, 0, false, '9,223,372,036,854,775,807'];
        yield 'int min' => [PHP_INT_MIN, 0, false, '-9,223,372,036,854,775,808'];
        yield 'large float' => [1.5e15, 0, false, '1,500,000,000,000,000'];
    }

    /**
     * @dataProvider formatProvider
     */
    public function testFormat(int|float $number, int $decimals, bool $trimZeros, string $expected): void
    {
        self::assertSame($expected, Number::format($number, $decimals, $trimZeros));
    }

    public function testFormatDefaults(): void
    {
        self::assertSame('1,234,568', Number::format(1234567.891));
    }

    /**
     * @dataProvider nonFiniteProvider
     */
    public function testFormatRejectsNonFinite(float $number): void
    {
        $this->expectException(InvalidArgumentException::class);

        Number::format($number);
    }

    /**
     * @return iterable<string, array{float}>
     */
    public static function nonFiniteProvider(): iterable
    {
        yield 'INF' => [INF];
        yield '-INF' => [-INF];
        yield 'NAN' => [NAN];
    }

    public function testFormatRejectsNegativeDecimals(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Number::format(1, -1);
    }

    public function testFormatWithLocale(): void
    {
        $this->requireIntl();

        self::assertSame('1,234,567.89', Number::format(1234567.891, 2, false, 'en_US'));
        self::assertSame('1.234.567,89', Number::format(1234567.891, 2, false, 'de_DE'));
        self::assertSame('1.234,5', Number::format(1234.5, 2, true, 'de_DE'));
        self::assertSame('-3', Number::format(-2.5, 0, false, 'en_US'), 'Intl rounds half away from zero too');
        self::assertSame('0.00', Number::format(-0.001, 2, false, 'en_US'));
    }

    public function testFormatWithLocaleMatchesBuiltInForEnglish(): void
    {
        $this->requireIntl();

        foreach ([0, 1, -1, 999, 1000, 1234567.891, -0.5, PHP_INT_MAX] as $number) {
            self::assertSame(Number::format($number, 2), Number::format($number, 2, false, 'en'));
        }
    }

    public function testFormatRejectsLocalesIntlRejects(): void
    {
        $this->requireIntl();

        // Older PHP/ICU builds fall back to the root locale; newer ones reject unknown locales.
        // Either way, no ValueError or IntlException leaks out.
        try {
            self::assertNotSame('', Number::format(1234.5, 1, false, 'not a locale!!'));
        } catch (InvalidArgumentException $exception) {
            self::assertStringContainsString('not a valid locale', $exception->getMessage());
        }
    }

    public function testFormatWithLocaleRequiresIntl(): void
    {
        $this->expectMissingIntl();

        Number::format(1, 0, false, 'de_DE');
    }

    /**
     * @return iterable<string, array{int|float, int, string}>
     */
    public static function compactProvider(): iterable
    {
        yield 'zero' => [0, 1, '0'];
        yield 'below thousand' => [999, 1, '999'];
        yield 'float below thousand' => [12.345, 1, '12.3'];
        yield 'thousand' => [1000, 1, '1K'];
        yield 'thousands with decimal' => [77700, 1, '77.7K'];
        yield 'no decimals' => [77700, 0, '78K'];
        yield 'two decimals' => [77370, 2, '77.37K'];
        yield 'million' => [1200000, 1, '1.2M'];
        yield 'half rounds up' => [1250000, 1, '1.3M'];
        yield 'trailing zero trimmed' => [1000000, 2, '1M'];
        yield 'carries to next unit' => [999950, 1, '1M'];
        yield 'carries without decimals' => [999500, 0, '1M'];
        yield 'stays below carry' => [999949, 1, '999.9K'];
        yield 'billion' => [3400000000, 1, '3.4B'];
        yield 'trillion' => [5000000000000, 1, '5T'];
        yield 'beyond trillion keeps T' => [1.5e15, 1, '1,500T'];
        yield 'negative' => [-1500, 1, '-1.5K'];
        yield 'negative rounds to zero' => [-0.04, 1, '0'];
        yield 'int max' => [PHP_INT_MAX, 1, '9,223,372T'];
        yield 'int min' => [PHP_INT_MIN, 1, '-9,223,372T'];
    }

    /**
     * @dataProvider compactProvider
     */
    public function testCompact(int|float $number, int $decimals, string $expected): void
    {
        self::assertSame($expected, Number::compact($number, $decimals));
    }

    public function testCompactDefaultsToOneDecimal(): void
    {
        self::assertSame('1.2M', Number::compact(1234567));
    }

    public function testCompactRejectsNonFinite(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Number::compact(INF);
    }

    /**
     * @return iterable<string, array{int|float, int, string}>
     */
    public static function percentageProvider(): iterable
    {
        yield 'fraction' => [0.75, 0, '75%'];
        yield 'zero' => [0, 0, '0%'];
        yield 'one' => [1, 0, '100%'];
        yield 'above one' => [2.5, 0, '250%'];
        yield 'negative' => [-0.1, 0, '-10%'];
        yield 'decimals' => [0.1234, 1, '12.3%'];
        yield 'rounding' => [0.12345, 2, '12.35%'];
        yield 'float noise hidden' => [0.07, 0, '7%'];
        yield 'half-up after multiplication noise' => [0.285, 0, '29%'];
        yield 'half-up after multiplication noise with decimals' => [0.14645, 2, '14.65%'];
        yield 'negative zero' => [-0.0001, 0, '0%'];
        yield 'thousands' => [12.5, 0, '1,250%'];
    }

    /**
     * @dataProvider percentageProvider
     */
    public function testPercentage(int|float $ratio, int $decimals, string $expected): void
    {
        self::assertSame($expected, Number::percentage($ratio, $decimals));
    }

    public function testPercentageWithLocale(): void
    {
        $this->requireIntl();

        self::assertSame('75%', Number::percentage(0.75, 0, 'en_US'));
        self::assertSame('12.3%', Number::percentage(0.1234, 1, 'en_US'));
        self::assertSame('75,6 %', self::normalizeSpaces(Number::percentage(0.756, 1, 'de_DE')));
        self::assertSame('0%', Number::percentage(-0.0001, 0, 'en_US'));
        self::assertSame(Number::percentage(0.285), Number::percentage(0.285, 0, 'en_US'));
    }

    public function testPercentageWithLocaleRequiresIntl(): void
    {
        $this->expectMissingIntl();

        Number::percentage(0.5, 0, 'en_US');
    }

    /**
     * @return iterable<string, array{int, string}>
     */
    public static function ordinalProvider(): iterable
    {
        yield '0' => [0, '0th'];
        yield '1' => [1, '1st'];
        yield '2' => [2, '2nd'];
        yield '3' => [3, '3rd'];
        yield '4' => [4, '4th'];
        yield '11' => [11, '11th'];
        yield '12' => [12, '12th'];
        yield '13' => [13, '13th'];
        yield '21' => [21, '21st'];
        yield '22' => [22, '22nd'];
        yield '23' => [23, '23rd'];
        yield '101' => [101, '101st'];
        yield '111' => [111, '111th'];
        yield '112' => [112, '112th'];
        yield '1001' => [1001, '1,001st'];
        yield '-1' => [-1, '-1st'];
        yield '-11' => [-11, '-11th'];
        yield '-22' => [-22, '-22nd'];
        yield 'int max' => [PHP_INT_MAX, '9,223,372,036,854,775,807th'];
        yield 'int min' => [PHP_INT_MIN, '-9,223,372,036,854,775,808th'];
    }

    /**
     * @dataProvider ordinalProvider
     */
    public function testOrdinal(int $number, string $expected): void
    {
        self::assertSame($expected, Number::ordinal($number));
    }

    public function testOrdinalWithLocale(): void
    {
        $this->requireIntl();

        self::assertSame('21st', Number::ordinal(21, 'en_US'));
        self::assertSame('21.', Number::ordinal(21, 'de_DE'));
        self::assertSame('21e', Number::ordinal(21, 'fr_FR'));
    }

    public function testOrdinalWithLocaleRequiresIntl(): void
    {
        $this->expectMissingIntl();

        Number::ordinal(1, 'en_US');
    }

    /**
     * @return iterable<string, array{int|float, string}>
     */
    public static function spellProvider(): iterable
    {
        yield 'zero' => [0, 'zero'];
        yield 'one' => [1, 'one'];
        yield 'teen' => [13, 'thirteen'];
        yield 'twenty' => [20, 'twenty'];
        yield 'hyphenated' => [21, 'twenty-one'];
        yield 'hundred' => [100, 'one hundred'];
        yield 'no and' => [101, 'one hundred one'];
        yield 'thousands' => [7721, 'seven thousand seven hundred twenty-one'];
        yield 'skips empty groups' => [1000001, 'one million one'];
        yield 'negative' => [-5, 'minus five'];
        $intMax = 'nine quintillion two hundred twenty-three quadrillion three hundred seventy-two trillion'
            . ' thirty-six billion eight hundred fifty-four million seven hundred seventy-five thousand eight hundred';
        yield 'int max' => [PHP_INT_MAX, $intMax . ' seven'];
        yield 'int min' => [PHP_INT_MIN, 'minus ' . $intMax . ' eight'];
        yield 'integral float' => [3.0, 'three'];
        yield 'float' => [1.5, 'one point five'];
        yield 'fraction digits one by one' => [3.14, 'three point one four'];
        yield 'below one' => [0.1, 'zero point one'];
        yield 'negative float' => [-0.25, 'minus zero point two five'];
        yield 'negative zero' => [-0.0, 'zero'];
        yield 'shortest float representation' => [0.1 + 0.2, 'zero point three' . str_repeat(' zero', 15) . ' four'];
        yield 'small exponent' => [2.5e-5, 'zero point zero zero zero zero two five'];
        yield 'large exponent' => [1e20, 'one hundred quintillion'];
        yield 'decillion' => [1e33, 'one decillion'];
    }

    /**
     * @dataProvider spellProvider
     */
    public function testSpell(int|float $number, string $expected): void
    {
        self::assertSame($expected, Number::spell($number));
    }

    public function testSpellRejectsTooLarge(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Number::spell(1e36);
    }

    public function testSpellRejectsNonFinite(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Number::spell(NAN);
    }

    public function testSpellMatchesIcuEnglish(): void
    {
        $this->requireIntl();

        $icu = new NumberFormatter('en', NumberFormatter::SPELLOUT);
        $numbers = array_merge(
            range(-30, 130),
            [999, 1000, 1001, 1010, 7721, 100000, 1000001, 123456789, 1.5, 0.1, -0.25, 3.14, 2.5e-5]
        );

        foreach ($numbers as $number) {
            self::assertSame($icu->format($number), Number::spell($number), "Spelling of {$number}");
        }
    }

    public function testSpellWithLocale(): void
    {
        $this->requireIntl();

        self::assertSame('twenty-one', Number::spell(21, 'en'));
        self::assertSame('vingt-et-un', Number::spell(21, 'fr'));
        self::assertStringNotContainsString('و ', Number::spell(21, 'ar'), 'Arabic "و" attaches to the next word');
        self::assertStringContainsString('وعشرون', Number::spell(21, 'ar_EG'));
    }

    public function testSpellWithLocaleRequiresIntl(): void
    {
        $this->expectMissingIntl();

        Number::spell(1, 'en');
    }
}
