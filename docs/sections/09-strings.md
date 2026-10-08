## Strings

`Pharaonic\Readable\Str` has one helper, for the initials you show on avatars and badges.

### Initials

`Str::initials()` takes the first letter or digit of each whitespace-separated word and uppercases it:

```php title="example.php"
Str::initials('Moamen Eltouny');   // "ME"
Str::initials('moamen eltouny');   // "ME"
Str::initials('Raggi');            // "R"
Str::initials('élise ößler');      // "ÉÖ"
Str::initials('3M Company');       // "3C"
```

With more words than `$limit` (`2` by default), it keeps the first `$limit - 1` words and the last one:

```php title="example.php"
Str::initials('John Ronald Reuel Tolkien');     // "JT"
Str::initials('John Ronald Reuel Tolkien', 3);  // "JRT"
Str::initials('John Ronald Reuel Tolkien', 1);  // "J"
```

Leading punctuation is skipped (`'(Ada) "Lovelace"'` → `"AL"`), words with no letters or digits are ignored (`'Ada & Lovelace'` → `"AL"`), a hyphenated word counts as one word, and combining marks stay with their letter. An empty or whitespace-only name returns `""`.

:::warning Invalid input
A `$limit` below `1` or a name that isn't valid UTF-8 throws `InvalidArgumentException`.
:::
