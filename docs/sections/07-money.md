## Money

`Pharaonic\Readable\Money` formats amounts for display. It does no arithmetic and no exchange rates.

### Formatting

Without a locale, the ISO 4217 code comes first and the currency's minor units decide the decimals:

```php title="example.php"
Money::format(100, 'USD');      // "USD 100.00"
Money::format(1234.5, 'USD');   // "USD 1,234.50"
Money::format(1234.5, 'JPY');   // "JPY 1,235"
Money::format(1.5, 'KWD');      // "KWD 1.500"
Money::format(-20, 'EGP');      // "-EGP 20.00"
Money::format(1, 'usd');        // "USD 1.00"
```

Override the decimals with `$decimals`:

```php title="example.php"
Money::format(1234.5, 'USD', decimals: 0);  // "USD 1,235"
Money::format(1234.5, 'JPY', decimals: 2);  // "JPY 1,234.50"
```

### With a locale

Intl picks the symbol, its position and the separators:

```php title="example.php"
Money::format(1234.5, 'USD', locale: 'en_US');   // "$1,234.50"
Money::format(-1234.5, 'USD', locale: 'en_US');  // "-$1,234.50"
Money::format(1234.5, 'JPY', locale: 'en_US');   // "¥1,235"
Money::format(1234.5, 'EUR', locale: 'de_DE');   // "1.234,50 €"
```

### Compact amounts

```php title="example.php"
Money::compact(1500000, 'USD');  // "USD 1.5M"
Money::compact(-2500, 'EUR');    // "-EUR 2.5K"
Money::compact(999, 'EGP');      // "EGP 999"
```

### Minor units

`Money::fractionDigits()` returns the ISO 4217 number of decimals for a currency:

```php title="example.php"
Money::fractionDigits('USD');  // 2
Money::fractionDigits('JPY');  // 0
Money::fractionDigits('KWD');  // 3
Money::fractionDigits('CLF');  // 4
```

Currencies with 0 decimals: `BIF`, `CLP`, `DJF`, `GNF`, `ISK`, `JPY`, `KMF`, `KRW`, `PYG`, `RWF`, `UGX`, `UYI`, `VND`, `VUV`, `XAF`, `XOF`, `XPF`. With 3: `BHD`, `IQD`, `JOD`, `KWD`, `LYD`, `OMR`, `TND`. With 4: `CLF`, `UYW`. Every other code uses 2.

:::warning Currency codes
The currency must be three letters (any case). Anything else, such as `"$"` or `"dollars"`, throws `InvalidArgumentException`. Well-formed codes that aren't in ISO 4217 are accepted and use 2 decimals.
:::
