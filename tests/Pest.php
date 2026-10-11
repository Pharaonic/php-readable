<?php

declare(strict_types=1);

/**
 * ICU separates parts with (narrow) no-break spaces whose exact choice varies between ICU versions.
 */
function normalizeSpaces(string $value): string
{
    return str_replace(["\u{a0}", "\u{202f}"], ' ', $value);
}

function withoutIntl(): bool
{
    return ! extension_loaded('intl');
}

function thrownBy(callable $callback): Throwable
{
    try {
        $callback();
    } catch (Throwable $exception) {
        return $exception;
    }

    throw new LogicException('Nothing was thrown.');
}
