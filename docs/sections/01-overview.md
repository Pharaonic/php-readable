:::badges
- PHP Package {color=blue}
- {release.label} {color=green}
- {package.license} License {color=purple}
:::

# Readable

Human-friendly formatting for plain PHP. Readable turns raw values into text people can read at a glance: `1250000` becomes `1.3M`, `1536` bytes become `1.5 KiB`, `3661` seconds become `1 hour 1 minute 1 second`. Each kind of value has its own small class of static methods, the output is the same on every machine, and Intl locales are there when you need them.

:::features
### Numbers {icon="chart"}
Grouped, compact, percentage, ordinal and spelled-out numbers: 1,234,567, 1.2M, 75%, 21st and twenty-one.

### Byte Sizes {icon="database"}
1500 bytes become 1.5 KB, 1536 bytes become 1.5 KiB, and "10 MB" parses back to 10000000.

### Money {icon="tag"}
1234.5 USD becomes USD 1,234.50, with the ISO 4217 minor units of every currency.

### Durations {icon="clock"}
3661 seconds become 1 hour 1 minute 1 second, and two dates give a calendar-exact difference.

### Strings & Arrays {icon="code"}
Initials for avatars (Moamen Eltouny becomes ME), plus null, nested and list checks for arrays.

### Optional Locales {icon="translate"}
Pass a locale such as de_DE to format with Intl, or leave it out for a deterministic English formatter.
:::

:::info Quick Tip
Every class lives in the `Pharaonic\Readable` namespace, so one grouped import covers them all: `use Pharaonic\Readable\{Arr, Bytes, Duration, Money, Number, Str};`
:::
