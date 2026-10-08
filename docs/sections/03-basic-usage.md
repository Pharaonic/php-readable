## Basic Usage

Import the class for the kind of value you have and call a static method. Every formatter returns a `string`.

```php title="example.php"
use Pharaonic\Readable\{Arr, Bytes, Duration, Money, Number, Str};

Number::format(1234567.891, 2);   // "1,234,567.89"
Number::compact(1250000);         // "1.3M"
Bytes::format(1500);              // "1.5 KB"
Money::format(1234.5, 'USD');     // "USD 1,234.50"
Duration::format(3661);           // "1 hour 1 minute 1 second"
Str::initials('Moamen Eltouny');  // "ME"
Arr::isList([10, 20, 30]);        // true
```

### Named arguments

Optional arguments come after the value, so named arguments keep calls readable when you skip some of them:

```php title="example.php"
Number::format(70.50, 2, trimZeros: true);  // "70.5"
Bytes::format(1536, binary: true);          // "1.5 KiB"
Duration::format(90061, parts: 2);          // "1 day 1 hour"
Money::format(1234.5, 'USD', decimals: 0);  // "USD 1,235"
```

### Rules shared by every formatter

- **Rounding** is half away from zero, like `number_format()`: `Number::format(2.5)` is `"3"` and `Number::format(-2.5)` is `"-3"`.
- **No negative zero.** A negative value that rounds to zero prints as zero: `Number::format(-0.001, 2)` is `"0.00"`.
- **Invalid input throws `InvalidArgumentException`:** `INF`, `NAN`, negative decimals, a malformed currency code, an unparsable size, or a date string PHP cannot read.
- **No state.** There is no global configuration; everything a call needs is in its arguments.
