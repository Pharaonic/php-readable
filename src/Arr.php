<?php

declare(strict_types=1);

namespace Pharaonic\Readable;

/**
 * Array inspection helpers.
 */
final class Arr
{
    /**
     * Determine whether every value is null. An empty array has no non-null value, so it returns true.
     *
     * @param array<mixed> $array
     */
    public static function isNull(array $array): bool
    {
        return !array_any($array, static fn (mixed $value): bool => $value !== null);
    }

    /**
     * Determine whether at least one value is an array (empty arrays included).
     *
     * @param array<mixed> $array
     */
    public static function isMultidimensional(array $array): bool
    {
        return array_any($array, static fn (mixed $value): bool => is_array($value));
    }

    /**
     * Determine whether the keys are 0, 1, 2, ... in order. An empty array is a list.
     *
     * @param array<mixed> $array
     */
    public static function isList(array $array): bool
    {
        return array_is_list($array);
    }
}
