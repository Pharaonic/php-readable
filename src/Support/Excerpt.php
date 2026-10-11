<?php

declare(strict_types=1);

namespace Pharaonic\Readable\Support;

/**
 * @internal
 */
final class Excerpt
{
    private const MAX_BYTES = 50;

    /**
     * Quote a caller-supplied value for an exception message, cut to a few dozen bytes so a huge input
     * never ends up whole in a message or a log.
     */
    public static function quote(string $value): string
    {
        if (strlen($value) <= self::MAX_BYTES) {
            return '"' . $value . '"';
        }

        return '"' . mb_strcut($value, 0, self::MAX_BYTES, 'UTF-8') . '…"';
    }
}
