<?php

declare(strict_types=1);

namespace Pharaonic\Readable\Tests;

use Pharaonic\Readable\Arr;
use PHPUnit\Framework\TestCase;

final class ArrTest extends TestCase
{
    /**
     * @return iterable<string, array{array<mixed>, bool}>
     */
    public static function nullProvider(): iterable
    {
        yield 'empty array is vacuously null' => [[], true];
        yield 'single null' => [[null], true];
        yield 'all null' => [[null, null], true];
        yield 'string keys all null' => [['a' => null, 'b' => null], true];
        yield 'null and zero' => [[null, 0], false];
        yield 'false is not null' => [[false], false];
        yield 'empty string is not null' => [[''], false];
        yield 'empty nested array is not null' => [[[]], false];
        yield 'nested nulls are not null' => [[[null]], false];
    }

    /**
     * @dataProvider nullProvider
     * @param array<mixed> $array
     */
    public function testIsNull(array $array, bool $expected): void
    {
        self::assertSame($expected, Arr::isNull($array));
    }

    /**
     * @return iterable<string, array{array<mixed>, bool}>
     */
    public static function multidimensionalProvider(): iterable
    {
        yield 'empty' => [[], false];
        yield 'flat list' => [[1, 2], false];
        yield 'flat map' => [['a' => 1, 'b' => null], false];
        yield 'only nested' => [[[1]], true];
        yield 'mixed' => [[1, [2]], true];
        yield 'nested map' => [['x' => ['y' => 1]], true];
        yield 'empty nested array' => [['a' => []], true];
        yield 'object is not an array' => [[new \ArrayObject([1])], false];
    }

    /**
     * @dataProvider multidimensionalProvider
     * @param array<mixed> $array
     */
    public function testIsMultidimensional(array $array, bool $expected): void
    {
        self::assertSame($expected, Arr::isMultidimensional($array));
    }

    /**
     * @return iterable<string, array{array<mixed>, bool}>
     */
    public static function listProvider(): iterable
    {
        yield 'empty' => [[], true];
        yield 'implicit keys' => [[1, 2], true];
        yield 'explicit sequential keys' => [[0 => 'a', 1 => 'b'], true];
        yield 'numeric string key is cast to int by PHP' => [['0' => 'a'], true];
        yield 'starts at one' => [[1 => 'a'], false];
        yield 'gap' => [[0 => 'a', 2 => 'b'], false];
        yield 'out of order' => [[1 => 'b', 0 => 'a'], false];
        yield 'string key' => [['a' => 1], false];
        yield 'negative key' => [[-1 => 'a'], false];
    }

    /**
     * @dataProvider listProvider
     * @param array<mixed> $array
     */
    public function testIsList(array $array, bool $expected): void
    {
        self::assertSame($expected, Arr::isList($array));
    }

    public function testIsListAfterUnset(): void
    {
        $array = [1, 2, 3];
        unset($array[1]);

        self::assertFalse(Arr::isList($array));
        self::assertTrue(Arr::isList(array_values($array)));
    }
}
