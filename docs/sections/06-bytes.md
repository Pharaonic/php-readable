## Byte Sizes

`Pharaonic\Readable\Bytes` formats and parses byte counts.

### Units

Two conventions, never mixed:

| Convention | Base | Units | How |
| --- | --- | --- | --- |
| SI decimal | 1000 | `B`, `KB`, `MB`, `GB`, `TB`, `PB`, `EB`, `ZB`, `YB` | default |
| IEC binary | 1024 | `B`, `KiB`, `MiB`, `GiB`, `TiB`, `PiB`, `EiB`, `ZiB`, `YiB` | `binary: true` |

### Formatting

`Bytes::format()` keeps at most `$decimals` fraction digits (`2` by default) and drops trailing zeros:

```php title="example.php"
Bytes::format(0);                    // "0 B"
Bytes::format(1000);                 // "1 KB"
Bytes::format(1024);                 // "1.02 KB"
Bytes::format(1500);                 // "1.5 KB"
Bytes::format(1536, binary: true);   // "1.5 KiB"
Bytes::format(1073741824, binary: true); // "1 GiB"
Bytes::format(1234, 1);              // "1.2 KB"
Bytes::format(-2048, binary: true);  // "-2 KiB"
```

Rounding can move a value to the next unit: `Bytes::format(999999)` is `"1 MB"`, not `"1000 KB"`.

### Parsing

`Bytes::parse()` turns a readable size back into an `int`. Units are case-insensitive, `KB` is decimal and `KiB` is binary, the same as `format()`:

```php title="example.php"
Bytes::parse('512');        // 512
Bytes::parse('512 bytes');  // 512
Bytes::parse('10 MB');      // 10000000
Bytes::parse('10mb');       // 10000000
Bytes::parse('1.5 KiB');    // 1536
Bytes::parse('2 GiB');      // 2147483648
```

Whole numbers are multiplied exactly; fractional bytes are rounded half away from zero.

:::warning Rejected input
`Bytes::parse()` throws `InvalidArgumentException` for anything it can't read unambiguously: a prefix without `B` (`"10 K"`), bits (`"10 Mbit"`), thousands separators (`"1,000 KB"`), exponents (`"1e3 B"`), or a size that does not fit in an `int` (`"8 EiB"`).
:::
