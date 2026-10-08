## Locales & Intl

`Number::format()`, `percentage()`, `ordinal()`, `spell()`, `Bytes::format()` and `Money::format()` accept an optional `?string $locale` as their last argument.

### Without a locale (default)

Readable uses its own English formatter: `,` for thousands, `.` for decimals, English suffixes and words. The output is identical on every machine, with or without the intl extension.

```php title="example.php"
Number::format(1234567.891, 2);  // "1,234,567.89"
Number::ordinal(22);             // "22nd"
Number::spell(7721);             // "seven thousand seven hundred twenty-one"
```

### With a locale

Readable formats through Intl's `NumberFormatter`, still rounding half away from zero:

```php title="example.php"
Number::format(1234567.891, 2, locale: 'de_DE');   // "1.234.567,89"
Number::percentage(0.756, 1, 'de_DE');             // "75,6 %"
Number::ordinal(21, 'fr');                         // "21e"
Number::spell(21, 'fr');                           // "vingt-et-un"
Money::format(1234.5, 'USD', locale: 'en_US');     // "$1,234.50"
Bytes::format(1500, locale: 'de_DE');              // "1,5 KB"
```

For Arabic locales, `Number::spell()` attaches the conjunction "و" to the following word: `Number::spell(21, 'ar')` returns `"واحد وعشرون"`.

:::warning Intl is required for locales
Passing a locale without `ext-intl` throws `Pharaonic\Readable\Exceptions\MissingIntlExtension` (a `RuntimeException`) instead of silently printing English. A locale that Intl itself rejects throws `InvalidArgumentException`.
:::

:::info Spaces in Intl output
Intl separates some parts with a no-break space (U+00A0) or a narrow no-break space (U+202F), for example in `"1.234,50 €"`. Keep that in mind when you compare strings in tests.
:::

`Number::compact()`, `Money::compact()`, `Duration` and `Str` have no locale argument: their output is always English.
