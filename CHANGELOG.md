# Changelog

All notable changes to this project are documented in this file.

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
