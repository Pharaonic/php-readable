## Durations

`Pharaonic\Readable\Duration` writes lengths of time in English.

### From seconds

```php title="example.php"
Duration::format(0);         // "0 seconds"
Duration::format(59);        // "59 seconds"
Duration::format(3661);      // "1 hour 1 minute 1 second"
Duration::format(187200);    // "2 days 4 hours"
Duration::format(604800);    // "1 week"
Duration::format(31536000);  // "1 year"
Duration::format(-3661);     // "1 hour 1 minute 1 second"
```

`format()` uses fixed-length units only: a year is 365 days, then weeks, days, hours, minutes and seconds. It never uses months, because a month has no fixed length: 29 days is `"4 weeks 1 day"`. The sign is ignored.

### Between two moments

`Duration::between()` uses the real calendar through PHP's `DateTimeInterface::diff()`, so it does report months. It accepts `DateTimeInterface` objects, Unix timestamps and any string `DateTimeImmutable` understands, in any order:

```php title="example.php"
Duration::between('2024-01-01', '2025-03-15');  // "1 year 2 months 2 weeks"
Duration::between('2024-02-01', '2024-03-01');  // "1 month"
Duration::between(0, 3661);                     // "1 hour 1 minute 1 second"
Duration::between($order->created_at, new DateTimeImmutable());
```

Moments in different time zones are compared as instants.

:::warning Daylight saving time
Within one time zone, `between()` counts on the wall clock: 12:00 to 12:00 across a DST change is `"1 day"`, and 00:00 → 06:00 on a spring-forward day is `"6 hours"`, even though 5 hours elapsed.
:::

### Options

Both methods take the same three options after their main arguments:

| Option | Default | Effect |
| --- | --- | --- |
| `?int $parts` | `null` (all) | Keep only the largest non-zero units. Lower units are cut off, not rounded. |
| `bool $short` | `false` | Short units: `y`, `mo`, `w`, `d`, `h`, `m`, `s`. |
| `string $separator` | `' '` | Text between units. |

```php title="example.php"
Duration::format(90061, parts: 2);         // "1 day 1 hour"
Duration::format(7199, parts: 2);          // "1 hour 59 minutes"
Duration::format(3661, short: true);       // "1h 1m 1s"
Duration::format(3661, separator: ', ');   // "1 hour, 1 minute, 1 second"
Duration::between('2024-01-01', '2025-03-15', parts: 2, short: true); // "1y 2mo"
```

`parts` must be `1` or greater; `0` throws `InvalidArgumentException`.
