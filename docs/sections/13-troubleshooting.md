## Troubleshooting

### `MissingIntlExtension` is thrown

You passed a `$locale` but the intl extension isn't loaded. Install and enable `ext-intl`, or drop the locale argument to use the built-in English formatter. Check with `php -m | grep intl`.

### A string comparison with Intl output fails

Intl puts a no-break space (U+00A0) or a narrow no-break space (U+202F) in some formats, such as `"1.234,50 €"` or `"75,6 %"`. Compare against the same characters, or replace them with a normal space before comparing. The exact space can change between ICU versions.

### `Bytes::format(1024)` returns `"1.02 KB"`

`KB` is 1000 bytes. For 1024-based units pass `binary: true`: `Bytes::format(1024, binary: true)` returns `"1 KiB"`.

### `Number::percentage(75)` returns `"7,500%"`

`percentage()` expects a ratio where `1` is 100%. Pass `0.75`, or divide your percent value by 100.

### `Duration::format()` never shows months

Months have no fixed length, so a bare number of seconds can't be split into them correctly. Use `Duration::between()` with two dates when you need calendar months.

### `Bytes::parse()` throws for `"10K"` or `"10 Mbit"`

A unit prefix needs a `B` (`10 KB`, `10 KiB`), and bits aren't bytes. Parsing also rejects thousands separators, exponents and sizes that don't fit in an `int`.

### `Money::format()` throws for `"$"` or `"dollars"`

The currency must be a three-letter ISO 4217 code such as `USD`. Map symbols or names to codes before formatting.

### `Duration::between()` throws for a date string

The string must be something `new DateTimeImmutable($string)` can read. Pass a `DateTimeInterface` or a Unix timestamp when your input uses another format.
