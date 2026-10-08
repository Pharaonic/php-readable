## API Reference

Every method is `public static`. Methods marked *throws* raise `InvalidArgumentException` for invalid input; any method with a `$locale` raises `Pharaonic\Readable\Exceptions\MissingIntlExtension` when a locale is passed without `ext-intl`.

### `Pharaonic\Readable\Number`

| Method | Description | Returns |
| --- | --- | --- |
| `format(int\|float $number, int $decimals = 0, bool $trimZeros = false, ?string $locale = null)` | Group thousands with a fixed number of decimals; `trimZeros` drops trailing fraction zeros. | `string` |
| `compact(int\|float $number, int $decimals = 1)` | Shorten with `K`, `M`, `B` or `T`, at most `$decimals` fraction digits. | `string` |
| `percentage(int\|float $ratio, int $decimals = 0, ?string $locale = null)` | Format a ratio (`1` = 100%) as a percentage. | `string` |
| `ordinal(int $number, ?string $locale = null)` | `1st`, `2nd`, `3rd`, `4th`, … | `string` |
| `spell(int\|float $number, ?string $locale = null)` | The number in words. | `string` |

### `Pharaonic\Readable\Bytes`

| Method | Description | Returns |
| --- | --- | --- |
| `format(int\|float $bytes, int $decimals = 2, bool $binary = false, ?string $locale = null)` | SI (`KB`) or, with `$binary`, IEC (`KiB`) size. | `string` |
| `parse(string $size)` | Readable size back to bytes, e.g. `'10 MB'` → `10000000`. *Throws.* | `int` |

### `Pharaonic\Readable\Money`

| Method | Description | Returns |
| --- | --- | --- |
| `format(int\|float $amount, string $currency, ?int $decimals = null, ?string $locale = null)` | Amount with its ISO 4217 code (or the locale's symbol); `$decimals` overrides the currency's minor units. *Throws.* | `string` |
| `compact(int\|float $amount, string $currency, int $decimals = 1)` | Short amount, e.g. `USD 1.5M`. *Throws.* | `string` |
| `fractionDigits(string $currency)` | ISO 4217 minor units of a currency. *Throws.* | `int` |

### `Pharaonic\Readable\Duration`

| Method | Description | Returns |
| --- | --- | --- |
| `format(int $seconds, ?int $parts = null, bool $short = false, string $separator = ' ')` | Seconds in years (365 days), weeks, days, hours, minutes and seconds. *Throws.* | `string` |
| `between(DateTimeInterface\|int\|string $start, DateTimeInterface\|int\|string $end, ?int $parts = null, bool $short = false, string $separator = ' ')` | Calendar difference between two moments, in any order. *Throws.* | `string` |

### `Pharaonic\Readable\Str`

| Method | Description | Returns |
| --- | --- | --- |
| `initials(string $name, int $limit = 2)` | Uppercase initials of a name. *Throws.* | `string` |

### `Pharaonic\Readable\Arr`

| Method | Description | Returns |
| --- | --- | --- |
| `isNull(array $array)` | Every value is `null` (`true` for `[]`). | `bool` |
| `isMultidimensional(array $array)` | At least one value is an array. | `bool` |
| `isList(array $array)` | Keys are `0, 1, 2, …` in order. | `bool` |

### Exceptions

| Class | Extends | Thrown when |
| --- | --- | --- |
| `Pharaonic\Readable\Exceptions\MissingIntlExtension` | `RuntimeException` | A `$locale` is passed but `ext-intl` isn't loaded. |
| `InvalidArgumentException` (PHP) | `LogicException` | `INF`/`NAN`, negative decimals, `parts` or `limit` below 1, a malformed currency code, an unparsable size or date, invalid UTF-8, or a locale Intl rejects. |
