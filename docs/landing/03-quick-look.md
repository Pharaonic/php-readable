---
view: components.packages.quick-look
title: A quick look
subtitle: One static call per value, wherever you render it.
file: src/Report.php
language: php
code: |
  use Pharaonic\Readable\{Bytes, Duration, Money, Number};

  Number::compact(1250000);              // "1.3M"
  Number::percentage(0.756, 1);          // "75.6%"
  Number::ordinal(21);                   // "21st"
  Bytes::format(1536, binary: true);     // "1.5 KiB"
  Money::format(1234.5, 'USD');          // "USD 1,234.50"
  Money::format(1234.5, 'EUR', locale: 'de_DE'); // "1.234,50 €"
  Duration::format(90061, parts: 2);     // "1 day 1 hour"
---
