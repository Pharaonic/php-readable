<?php

declare(strict_types=1);

namespace Pharaonic\Readable\Support;

use IntlException;
use InvalidArgumentException;
use NumberFormatter;
use Pharaonic\Readable\Exceptions\MissingIntlExtension;
use ValueError;

/**
 * @internal
 */
final class Intl
{
    /**
     * Create a NumberFormatter that rounds half away from zero, like number_format().
     *
     * The style is a NumberFormatter constant name ("DECIMAL", "CURRENCY", ...), so callers never touch
     * the class before the extension check.
     *
     * @param 'DECIMAL'|'PERCENT'|'CURRENCY'|'ORDINAL'|'SPELLOUT' $style
     * @throws MissingIntlExtension
     */
    public static function formatter(string $locale, string $style): NumberFormatter
    {
        if (!extension_loaded('intl')) {
            throw MissingIntlExtension::forLocale($locale);
        }

        $style = match ($style) {
            'DECIMAL' => NumberFormatter::DECIMAL,
            'PERCENT' => NumberFormatter::PERCENT,
            'CURRENCY' => NumberFormatter::CURRENCY,
            'ORDINAL' => NumberFormatter::ORDINAL,
            'SPELLOUT' => NumberFormatter::SPELLOUT,
        };

        // Newer PHP/ICU builds reject unknown locales (ValueError, IntlException); older ones fall back to root.
        try {
            $formatter = new NumberFormatter($locale, $style);
        } catch (ValueError | IntlException $exception) {
            throw new InvalidArgumentException(sprintf('"%s" is not a valid locale.', $locale), 0, $exception);
        }
        $formatter->setAttribute(NumberFormatter::ROUNDING_MODE, NumberFormatter::ROUND_HALFUP);

        return $formatter;
    }

    /**
     * Fix the number of fraction digits, or allow fewer when $trimZeros is set.
     */
    public static function fractionDigits(NumberFormatter $formatter, int $decimals, bool $trimZeros = false): void
    {
        $formatter->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, $trimZeros ? 0 : $decimals);
        $formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, $decimals);
    }

    public static function result(string|false $result, NumberFormatter $formatter): string
    {
        if ($result === false) {
            throw new InvalidArgumentException('Intl formatting failed: ' . $formatter->getErrorMessage());
        }

        return $result;
    }
}
