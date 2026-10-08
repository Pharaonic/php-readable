<?php

declare(strict_types=1);

namespace Pharaonic\Readable;

use InvalidArgumentException;

/**
 * Human-friendly string helpers.
 */
final class Str
{
    /**
     * Get the uppercase initials of a name: "Moamen Eltouny" becomes "ME".
     *
     * Each whitespace-separated word contributes its first letter or digit (with any combining
     * marks). When there are more words than $limit, the first ($limit - 1) words and the last
     * word are used, so "John Ronald Reuel Tolkien" becomes "JT".
     */
    public static function initials(string $name, int $limit = 2): string
    {
        if ($limit < 1) {
            throw new InvalidArgumentException(sprintf('Limit must be 1 or greater, %d given.', $limit));
        }

        if (preg_match('//u', $name) !== 1) {
            throw new InvalidArgumentException('The name is not valid UTF-8.');
        }

        $initials = [];

        foreach (preg_split('/[\s\p{Z}]+/u', $name, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $word) {
            if (preg_match('/[\p{L}\p{N}]\p{M}*/u', $word, $match) === 1) {
                $initials[] = $match[0];
            }
        }

        if (count($initials) > $limit) {
            $initials = $limit === 1
                ? [$initials[0]]
                : array_merge(array_slice($initials, 0, $limit - 1), [$initials[count($initials) - 1]]);
        }

        return mb_strtoupper(implode('', $initials), 'UTF-8');
    }
}
