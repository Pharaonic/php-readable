# Maintaining This Package

This file records package-specific maintainer facts that are safe to keep in the public repository.

## Purpose

Framework-agnostic, human-friendly formatting for PHP: numbers, bytes, money and durations, plus small array and string inspection helpers. Slugs belong to `php-slugify`, Hijri dates to `php-hijri`, and framework integration to framework packages such as `laravel-readable`.

## Supported PHP lines

One branch per PHP minor, each requiring exactly that minor (`"php": ">=8.N <8.N+1"`):

```text
8.0.x  8.1.x  8.2.x  8.3.x  8.4.x  8.5.x
```

`develop` follows the newest line. Each line uses the native functions of its own PHP (`array_is_list()` from 8.1, typed constants and exact-integer `number_format()` from 8.3, `array_any()` from 8.4, `array_first()`/`array_last()` from 8.5), so a ported change is rewritten for the target line rather than copied.

## Public API invariants

- The public API is the static methods of `Arr`, `Str`, `Number`, `Bytes`, `Money` and `Duration`, and `Exceptions\MissingIntlExtension`. Everything under `Support\` is `@internal`.
- Without a `$locale`, output comes from the built-in English formatter and is identical on every machine, with or without `ext-intl`.
- With a `$locale`, `ext-intl` is used; without it, `MissingIntlExtension` is thrown. Invalid locales never leak `ValueError` or `IntlException`.
- Rounding is half away from zero everywhere, a rounded value carries to the next unit (`999 999 B` is `1 MB`), and no formatter prints negative zero.
- Invalid input throws the native `InvalidArgumentException`. `$decimals` accepts 0 to 100, and exception messages quote at most 50 bytes of the rejected value.
- Output changes are behavior changes: they need a changelog entry and must not land in a patch on an existing line unless they fix a bug.

## Package-specific constraints

- Only `ext-mbstring` is required at runtime; `ext-intl` stays a `suggest`.
- CI runs the tests on Linux and Windows, with lowest and highest dependencies, and once without `ext-intl`.

## Branch notes

Tooling follows the line's PHP: Pest 1 on `8.0.x` and `8.1.x`, Pest 3 on `8.2.x`, Pest 4 from `8.3.x`; Pint uses the `psr12` preset on `8.0.x` and `per` on the other lines.

## Documentation

https://pharaonic.dev/packages/php/readable

Keep this file factual and package-specific. General Pharaonic engineering policy belongs in the private engineering repository, not here.
