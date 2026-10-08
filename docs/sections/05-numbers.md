## Numbers

`Pharaonic\Readable\Number` formats integers and floats.

### Grouped numbers

`Number::format()` groups thousands and fixes the number of decimals (`0` by default):

```php title="example.php"
Number::format(1234567);           // "1,234,567"
Number::format(60708.547, 2);      // "60,708.55"
Number::format(70, 2);             // "70.00"
Number::format(-1234567);          // "-1,234,567"
Number::format(PHP_INT_MAX);       // "9,223,372,036,854,775,807"
```

With `trimZeros: true`, trailing fraction zeros are dropped:

```php title="example.php"
Number::format(70.50, 2, trimZeros: true);  // "70.5"
Number::format(70.00, 2, trimZeros: true);  // "70"
Number::format(70.07, 2, trimZeros: true);  // "70.07"
```

:::info Custom separators
For other separators without a locale, use PHP's own `number_format($number, 2, ',', '.')`.
:::

### Compact numbers

`Number::compact()` shortens a number with a `K`, `M`, `B` or `T` suffix, keeping at most `$decimals` fraction digits (`1` by default) and dropping trailing zeros:

```php title="example.php"
Number::compact(999);         // "999"
Number::compact(77700);       // "77.7K"
Number::compact(77700, 0);    // "78K"
Number::compact(77370, 2);    // "77.37K"
Number::compact(1000000);     // "1M"
Number::compact(3400000000);  // "3.4B"
Number::compact(-1500);       // "-1.5K"
```

When rounding reaches the next unit, the next unit is used: `Number::compact(999950)` is `"1M"`, not `"1,000K"`. Values past 999 trillion keep the `T` suffix: `Number::compact(1.5e15)` is `"1,500T"`.

### Percentages

`Number::percentage()` takes a **ratio**, where `1` is 100%:

```php title="example.php"
Number::percentage(0.75);        // "75%"
Number::percentage(1);           // "100%"
Number::percentage(0.1234, 1);   // "12.3%"
Number::percentage(-0.1);        // "-10%"
```

:::warning Ratio, not percent
`Number::percentage(75)` is `"7,500%"`. Divide by 100 first if your value is already a percentage.
:::

### Ordinals

```php title="example.php"
Number::ordinal(1);     // "1st"
Number::ordinal(22);    // "22nd"
Number::ordinal(113);   // "113th"
Number::ordinal(1001);  // "1,001st"
Number::ordinal(-11);   // "-11th"
```

### Numbers in words

`Number::spell()` follows ICU's English rules: no "and", hyphenated tens, and fraction digits read one by one. It covers every `int` and floats up to the decillions.

```php title="example.php"
Number::spell(21);       // "twenty-one"
Number::spell(101);      // "one hundred one"
Number::spell(7721);     // "seven thousand seven hundred twenty-one"
Number::spell(-5);       // "minus five"
Number::spell(3.14);     // "three point one four"
Number::spell(1000001);  // "one million one"
```

Floats are read from their shortest exact representation, so `Number::spell(0.1)` is `"zero point one"`.
