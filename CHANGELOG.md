# Changelog

All notable changes to this project are documented in this file.

## 8.3.0 - Unreleased

The `8.3.x` line targets PHP 8.3. Output is identical to `8.2.0`.

### Changed

- **Requires PHP `>=8.3 <8.4`.** Use the `8.2.x` line on PHP 8.2.
- Class constants are typed.
- Integers are formatted with `number_format()`, which keeps them exact since PHP 8.3, instead of a hand-written digit grouping.
- PHPStan analyses against PHP 8.3, and CI falls back to PHP 8.3 on branches that are not an `8.N.x` line.

## 8.2.0 - Unreleased

The `8.2.x` line targets PHP 8.2. Output is identical to `8.1.0`.

### Changed

- **Requires PHP `>=8.2 <8.3`.** Use the `8.1.x` line on PHP 8.1.
- PHPStan analyses against PHP 8.2, and CI falls back to PHP 8.2 on branches that are not an `8.N.x` line.

## 8.1.0 - Unreleased

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
