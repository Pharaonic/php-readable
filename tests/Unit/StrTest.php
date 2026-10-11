<?php

declare(strict_types=1);

use Pharaonic\Readable\Str;

it('returns the initials of a name', function (string $name, int $limit, string $expected) {
    expect(Str::initials($name, $limit))->toBe($expected);
})->with([
    'two words' => ['Moamen Eltouny', 2, 'ME'],
    'lowercase is uppercased' => ['moamen eltouny', 2, 'ME'],
    'single word' => ['Raggi', 2, 'R'],
    'first and last of many' => ['John Ronald Reuel Tolkien', 2, 'JT'],
    'limit three' => ['John Ronald Reuel Tolkien', 3, 'JRT'],
    'limit one' => ['John Ronald Reuel Tolkien', 1, 'J'],
    'limit above word count' => ['Ada Lovelace', 5, 'AL'],
    'empty' => ['', 2, ''],
    'whitespace only' => ["  \t\n ", 2, ''],
    'extra whitespace' => ["  Ada \t  Lovelace  ", 2, 'AL'],
    'leading punctuation skipped' => ['(Ada) "Lovelace"', 2, 'AL'],
    'symbol-only word ignored' => ['Ada & Lovelace', 2, 'AL'],
    'digits count' => ['3M Company', 2, '3C'],
    'hyphenated word is one word' => ['Jean-Luc Picard', 2, 'JP'],
    'unicode uppercase' => ['élise ößler', 2, 'ÉÖ'],
    'arabic' => ['مؤمن التوني', 2, 'ما'],
    'combining mark kept' => ["e\u{301}lise Durand", 2, "E\u{301}D"],
    'non-breaking space separates' => ["Ada\u{a0}Lovelace", 2, 'AL'],
]);

it('returns two initials by default', function () {
    expect(Str::initials('Ada Byron King Countess'))->toBe('AC');
});

it('rejects a zero initials limit', function () {
    Str::initials('Ada Lovelace', 0);
})->throws(InvalidArgumentException::class);

it('rejects a name that is not valid UTF-8', function () {
    Str::initials("Ada \xff");
})->throws(InvalidArgumentException::class);
