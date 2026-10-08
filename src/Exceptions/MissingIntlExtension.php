<?php

declare(strict_types=1);

namespace Pharaonic\Readable\Exceptions;

use RuntimeException;

/**
 * Thrown when a locale-aware format is requested but ext-intl is not installed.
 */
final class MissingIntlExtension extends RuntimeException
{
    public static function forLocale(string $locale): self
    {
        return new self(sprintf(
            'Formatting for locale "%s" requires the intl extension. '
            . 'Install ext-intl or omit the locale to use the built-in English formatter.',
            $locale
        ));
    }
}
