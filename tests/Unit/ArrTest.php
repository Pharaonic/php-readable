<?php

declare(strict_types=1);

use Pharaonic\Readable\Arr;

it('checks whether every value is null', function (array $array, bool $expected) {
    expect(Arr::isNull($array))->toBe($expected);
})->with([
    'empty array is vacuously null' => [[], true],
    'single null' => [[null], true],
    'all null' => [[null, null], true],
    'string keys all null' => [['a' => null, 'b' => null], true],
    'null and zero' => [[null, 0], false],
    'false is not null' => [[false], false],
    'empty string is not null' => [[''], false],
    'empty nested array is not null' => [[[]], false],
    'nested nulls are not null' => [[[null]], false],
]);

it('checks whether an array is multidimensional', function (array $array, bool $expected) {
    expect(Arr::isMultidimensional($array))->toBe($expected);
})->with([
    'empty' => [[], false],
    'flat list' => [[1, 2], false],
    'flat map' => [['a' => 1, 'b' => null], false],
    'only nested' => [[[1]], true],
    'mixed' => [[1, [2]], true],
    'nested map' => [['x' => ['y' => 1]], true],
    'empty nested array' => [['a' => []], true],
    'object is not an array' => [[new ArrayObject([1])], false],
]);

it('checks whether an array is a list', function (array $array, bool $expected) {
    expect(Arr::isList($array))->toBe($expected);
})->with([
    'empty' => [[], true],
    'implicit keys' => [[1, 2], true],
    'explicit sequential keys' => [[0 => 'a', 1 => 'b'], true],
    'numeric string key is cast to int by PHP' => [['0' => 'a'], true],
    'starts at one' => [[1 => 'a'], false],
    'gap' => [[0 => 'a', 2 => 'b'], false],
    'out of order' => [[1 => 'b', 0 => 'a'], false],
    'string key' => [['a' => 1], false],
    'negative key' => [[-1 => 'a'], false],
]);

it('is not a list after a value is unset', function () {
    $array = [1, 2, 3];
    unset($array[1]);

    expect(Arr::isList($array))->toBeFalse()
        ->and(Arr::isList(array_values($array)))->toBeTrue();
});
