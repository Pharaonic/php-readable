<?php

declare(strict_types=1);

namespace Pharaonic\Readable\Tests;

use InvalidArgumentException;
use Pharaonic\Readable\Str;
use PHPUnit\Framework\TestCase;

final class StrTest extends TestCase
{
    /**
     * @return iterable<string, array{string, int, string}>
     */
    public static function initialsProvider(): iterable
    {
        yield 'two words' => ['Moamen Eltouny', 2, 'ME'];
        yield 'lowercase is uppercased' => ['moamen eltouny', 2, 'ME'];
        yield 'single word' => ['Raggi', 2, 'R'];
        yield 'first and last of many' => ['John Ronald Reuel Tolkien', 2, 'JT'];
        yield 'limit three' => ['John Ronald Reuel Tolkien', 3, 'JRT'];
        yield 'limit one' => ['John Ronald Reuel Tolkien', 1, 'J'];
        yield 'limit above word count' => ['Ada Lovelace', 5, 'AL'];
        yield 'empty' => ['', 2, ''];
        yield 'whitespace only' => ["  \t\n ", 2, ''];
        yield 'extra whitespace' => ["  Ada \t  Lovelace  ", 2, 'AL'];
        yield 'leading punctuation skipped' => ['(Ada) "Lovelace"', 2, 'AL'];
        yield 'symbol-only word ignored' => ['Ada & Lovelace', 2, 'AL'];
        yield 'digits count' => ['3M Company', 2, '3C'];
        yield 'hyphenated word is one word' => ['Jean-Luc Picard', 2, 'JP'];
        yield 'unicode uppercase' => ['élise ößler', 2, 'ÉÖ'];
        yield 'arabic' => ['مؤمن التوني', 2, 'ما'];
        yield 'combining mark kept' => ["e\u{301}lise Durand", 2, "E\u{301}D"];
        yield 'non-breaking space separates' => ["Ada\u{a0}Lovelace", 2, 'AL'];
    }

    /**
     * @dataProvider initialsProvider
     */
    public function testInitials(string $name, int $limit, string $expected): void
    {
        self::assertSame($expected, Str::initials($name, $limit));
    }

    public function testInitialsDefaultLimitIsTwo(): void
    {
        self::assertSame('AC', Str::initials('Ada Byron King Countess'));
    }

    public function testInitialsRejectsZeroLimit(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Str::initials('Ada Lovelace', 0);
    }

    public function testInitialsRejectsInvalidUtf8(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Str::initials("Ada \xff");
    }
}
