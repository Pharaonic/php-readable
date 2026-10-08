:::badges
- PHP Package {color=blue}
- {release.label} {color=green}
- {package.license} License {color=purple}
:::

# Readable

Human-friendly formatting for plain PHP. Readable turns raw values into text people can read at a glance: `1250000` becomes `1.3M`, `1536` bytes become `1.5 KiB`, `3661` seconds become `1 hour 1 minute 1 second`. Each kind of value has its own small class of static methods, the output is the same on every machine, and Intl locales are there when you need them.

:::features
### Numbers {icon="chart"}
`Number::format()`, `compact()`, `percentage()`, `ordinal()` and `spell()`: `1,234,567`, `1.2M`, `75%`, `21st`, `twenty-one`.

### Byte Sizes {icon="database"}
`Bytes::format(1500)` → `1.5 KB`, `Bytes::format(1536, binary: true)` → `1.5 KiB`, and `Bytes::parse('10 MB')` back to `10000000`.

### Money {icon="tag"}
`Money::format(1234.5, 'USD')` → `USD 1,234.50`, with ISO 4217 minor units for every currency.

### Durations {icon="clock"}
`Duration::format(3661)` → `1 hour 1 minute 1 second`, and `Duration::between()` for calendar-exact date differences.

### Strings & Arrays {icon="code"}
`Str::initials('Moamen Eltouny')` → `ME`, plus `Arr::isNull()`, `isMultidimensional()` and `isList()`.

### Optional Locales {icon="translate"}
Pass a locale such as `de_DE` to format with Intl; leave it out for a deterministic English formatter.
:::

:::info Quick Tip
Every class lives in the `Pharaonic\Readable` namespace, so one grouped import covers them all: `use Pharaonic\Readable\{Arr, Bytes, Duration, Money, Number, Str};`
:::
