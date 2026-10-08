<?php

declare(strict_types=1);

namespace Pharaonic\Readable\Tests;

use Pharaonic\Readable\Exceptions\MissingIntlExtension;
use PHPUnit\Framework\TestCase;

abstract class IntlTestCase extends TestCase
{
    protected function requireIntl(): void
    {
        if (!extension_loaded('intl')) {
            self::markTestSkipped('ext-intl is not installed.');
        }
    }

    protected function expectMissingIntl(): void
    {
        if (extension_loaded('intl')) {
            self::markTestSkipped('ext-intl is installed.');
        }

        $this->expectException(MissingIntlExtension::class);
    }

    /**
     * ICU separates parts with (narrow) no-break spaces whose exact choice varies between ICU versions.
     */
    protected static function normalizeSpaces(string $value): string
    {
        return str_replace(["\u{a0}", "\u{202f}"], ' ', $value);
    }
}
