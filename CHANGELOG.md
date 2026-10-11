# Changelog

All notable changes to this project are documented in this file.

## 8.2.2 - 2026-10-11

### Fixed

- `$decimals` above 100 throws `InvalidArgumentException` in `Number::format()`, `Number::compact()`, `Number::percentage()`, `Bytes::format()`, `Money::format()` and `Money::compact()`. Very large values used to exhaust memory with a fatal error, or return wrong digits with a locale: `Number::format(1.5, 100000000, false, 'en_US')` returned `"0"`.
- Exception messages quote at most 50 bytes of the value they reject, so a huge input no longer ends up whole in a message or a log.

### Compatibility

- Decimals from 0 to 100 format exactly as before.

## 8.2.1 - 2026-10-11

### Changed

- The development tooling moved to Pest, Pint and PHPStan at level `max`. `composer check` now validates `composer.json`, checks the code style, runs static analysis and the tests, and audits the dependencies. The library's output is unchanged.

## 8.2.0 - 2026-10-08

The `8.2.x` line targets PHP 8.2. Output is identical to `8.1.0`.

### Changed

- **Requires PHP `>=8.2 <8.3`.** Use the `8.1.x` line on PHP 8.1.
- PHPStan analyses against PHP 8.2, and CI falls back to PHP 8.2 on branches that are not an `8.N.x` line.

## 8.1.0 - 2026-10-08

The `8.1.x` line targets PHP 8.1. Output is identical to `8.0.1`, except for `Duration::between()` on days with a daylight saving time change.

### Changed

- **Requires PHP `>=8.1 <8.2`.** Use the `8.0.x` line on PHP 8.0.
- `Arr::isList()` uses the native `array_is_list()`.
- `Duration::between()` uses the native `DateTimeInterface::diff()` directly. Hours on a day with a DST change now count the time that actually elapsed: 00:00 → 06:00 on a spring-forward day is `"5 hours"` (was `"6 hours"`). Whole days are unchanged.
- The test suite fails on PHP deprecations.
- PHPStan analyses against PHP 8.1, and CI falls back to PHP 8.1 on branches that are not an `8.N.x` line.

## 8.0.1 - 2026-10-08

### Fixed

- Documentation: the overview feature cards lost their inline code examples on pharaonic.dev, leaving text such as ", , , and". They are now written as plain text.

## 8.0.0 - 2026-10-08

### Added

- Framework-agnostic, domain-based API under `Pharaonic\Readable`.
- `Arr::isNull()`, `Arr::isMultidimensional()` and `Arr::isList()`.
- `Str::initials()`.
- `Number::format()`, `Number::compact()`, `Number::percentage()`, `Number::ordinal()` and `Number::spell()`.
- `Bytes::format()` with SI (`KB`, default) and IEC (`KiB`) units, and `Bytes::parse()`.
- `Money::format()`, `Money::compact()` and `Money::fractionDigits()` with ISO 4217 minor units.
- `Duration::format()` and `Duration::between()`.
- Optional `ext-intl` support through a `$locale` argument; a built-in English formatter is used otherwise.
- Consistent half-up rounding, carry-over to the next unit (`999 999 B` is `1 MB`), and no negative zero (`-0.00`) in any formatter.

### Compatibility

- PHP 8.0 on the `8.0.x` line.
- No framework dependency; only `ext-mbstring` is required.
