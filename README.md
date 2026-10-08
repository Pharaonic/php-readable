<p align="center">
  <a href="https://php.net" target="_blank"><img src="https://img.shields.io/static/v1?label=PHP&message=8.0&color=blue&style=flat-square" alt="PHP Version : 8.0"></a>
  <img src="https://img.shields.io/static/v1?label=License&message=MIT&color=brightgreen&style=flat-square" alt="License">
  <img src="https://github.com/Pharaonic/php-readable/actions/workflows/build.yml/badge.svg" alt="Tests">
  <br>
  <a href="https://packagist.org/packages/pharaonic/php-readable" target="_blank"><img src="https://poser.pugx.org/pharaonic/php-readable/v" alt="Packagist Version"></a>
  <a href="https://packagist.org/packages/pharaonic/php-readable" target="_blank"><img src="https://poser.pugx.org/pharaonic/php-readable/downloads" alt="Packagist Downloads"></a>
</p>

<h3 align="center">Human-friendly numbers, sizes, money and durations for plain PHP.</h3>
<h5 align="center">Small focused classes, predictable output, no framework, optional Intl.</h5>
<br>

## Requirements

- PHP 8.0 on the `8.0.x` line (each `8.N.x` line targets PHP 8.N)
- `ext-mbstring`
- `ext-intl` (optional): needed only when you pass a `$locale`

## Installation

```bash
composer require pharaonic/php-readable
```

## Quick start

```php
use Pharaonic\Readable\{Arr, Bytes, Duration, Money, Number, Str};

Number::format(1234567.891, 2);   // "1,234,567.89"
Number::compact(1250000);         // "1.3M"
Bytes::format(1500);              // "1.5 KB"
Money::format(1234.5, 'USD');     // "USD 1,234.50"
Duration::format(3661);           // "1 hour 1 minute 1 second"
Str::initials('Moamen Eltouny');   // "ME"
Arr::isList([10, 20, 30]);        // true
```

### Locales

Every formatter that takes a `?string $locale` works the same way:

- `null` (default): a built-in English formatter with `,` thousands and `.` decimals. Output is identical on every machine, with or without Intl.
- A locale such as `'de_DE'`: Intl's `NumberFormatter` (rounding half away from zero, like `number_format()`). Without `ext-intl` this throws `Pharaonic\Readable\Exceptions\MissingIntlExtension` rather than printing the wrong language.

Invalid input (`INF`, `NAN`, negative decimals, a malformed currency code, an unparsable size) throws `InvalidArgumentException`.

## Number

```php
Number::format(1234567);                        // "1,234,567"
Number::format(60708.547, 2);                   // "60,708.55"
Number::format(70.50, 2, trimZeros: true);      // "70.5"
Number::format(70.00, 2, trimZeros: true);      // "70"
Number::format(1234567.891, 2, locale: 'de_DE'); // "1.234.567,89"

Number::compact(77700);       // "77.7K"
Number::compact(999950);      // "1M"   (rounding moves to the next unit)
Number::compact(-1500);       // "-1.5K"
Number::compact(77700, 0);    // "78K"

Number::percentage(0.75);     // "75%"  (input is a ratio: 1 = 100%)
Number::percentage(0.1234, 1); // "12.3%"

Number::ordinal(21);          // "21st"
Number::ordinal(113);         // "113th"
Number::ordinal(21, 'fr');    // "21e"

Number::spell(7721);          // "seven thousand seven hundred twenty-one"
Number::spell(-1.5);          // "minus one point five"
Number::spell(21, 'ar');      // "واحد وعشرون"
```

`compact()` uses English `K`, `M`, `B`, `T` suffixes. The built-in `spell()` follows ICU's English rules, so it gives the same words as `Number::spell($n, 'en')`.

## Bytes

SI decimal units by default (`1 KB = 1000 B`); IEC binary units with `binary: true` (`1 KiB = 1024 B`). The two are never mixed.

```php
Bytes::format(0);                    // "0 B"
Bytes::format(1500);                 // "1.5 KB"
Bytes::format(999999);               // "1 MB"
Bytes::format(1536, binary: true);   // "1.5 KiB"
Bytes::format(1234, 1);              // "1.2 KB"
Bytes::format(-2048, binary: true);  // "-2 KiB"

Bytes::parse('10 MB');    // 10000000
Bytes::parse('1.5 KiB');  // 1536
Bytes::parse('512');      // 512
```

## Money

Formatting only, no arithmetic. Without a locale the ISO code comes first and the currency's ISO 4217 minor units are used.

```php
Money::format(1234.5, 'USD');                   // "USD 1,234.50"
Money::format(1234.5, 'JPY');                   // "JPY 1,235"
Money::format(1.5, 'KWD');                      // "KWD 1.500"
Money::format(-20, 'EGP');                      // "-EGP 20.00"
Money::format(1234.5, 'USD', decimals: 0);      // "USD 1,235"
Money::format(1234.5, 'USD', locale: 'en_US');  // "$1,234.50"
Money::format(1234.5, 'EUR', locale: 'de_DE');  // "1.234,50 €"

Money::compact(1500000, 'USD');   // "USD 1.5M"
Money::fractionDigits('KWD');     // 3
```

## Duration

```php
Duration::format(3661);                    // "1 hour 1 minute 1 second"
Duration::format(90061, parts: 2);         // "1 day 1 hour"
Duration::format(3661, short: true);       // "1h 1m 1s"
Duration::format(3661, separator: ', ');   // "1 hour, 1 minute, 1 second"
Duration::format(0);                       // "0 seconds"

Duration::between('2024-01-01', '2025-03-15');   // "1 year 2 months 2 weeks"
Duration::between($createdAt, new DateTime());   // DateTimeInterface, timestamps or date strings
```

`format()` uses fixed-length units (year = 365 days, week, day, hour, minute, second) and never months, because a month has no fixed length. `between()` uses the real calendar, so it does report months. The sign and argument order are ignored, and `parts` truncates instead of rounding. Within one time zone, `between()` counts on the wall clock, so 00:00 → 06:00 on a spring-forward day is "6 hours".

## Str

```php
Str::initials('Moamen Eltouny');              // "ME"
Str::initials('John Ronald Reuel Tolkien');   // "JT" (first and last word)
Str::initials('John Ronald Reuel Tolkien', 3); // "JRT"
Str::initials('élise ößler');                 // "ÉÖ"
```

## Arr

```php
Arr::isNull([null, null]);              // true  ([] is true as well)
Arr::isNull([null, 0]);                 // false
Arr::isMultidimensional([1, [2]]);      // true  (['a' => []] is true as well)
Arr::isList([10, 20]);                  // true  ([] is true as well)
Arr::isList([1 => 10]);                 // false
```

## Framework integration

This package has no framework dependency: the classes are plain static methods, so they work in any PHP project or framework.

## Versioning

Each `8.N.x` branch targets exactly PHP 8.N and is released independently. Pick the line that matches your PHP version; Composer will do it for you.

## Testing

```bash
composer test      # PHPUnit
composer check     # coding style, PHPStan and tests
```

## Documentation

More examples are on [pharaonic.dev](https://pharaonic.dev/packages/php/readable).

## Security

See [SECURITY.md](SECURITY.md) for how to report a vulnerability.

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md).

## License

This package is open-source software licensed under the [MIT license](LICENSE).
