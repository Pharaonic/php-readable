## Examples

### 1. A file list

Show each upload's size and age next to its name.

```php title="src/Uploads/FileRow.php"
use Pharaonic\Readable\{Bytes, Duration};

function fileRow(string $path): string
{
    $size = Bytes::format(filesize($path), 1, binary: true);
    $age = Duration::between(filemtime($path), time(), parts: 1);

    return sprintf('%s · %s · %s ago', basename($path), $size, $age);
}

// "report.pdf · 2.4 MiB · 3 days ago"
```

### 2. Dashboard stat cards

Big numbers stay short and percentages stay honest about rounding.

```php title="src/Dashboard/Stats.php"
use Pharaonic\Readable\Number;

$cards = [
    'Visitors'   => Number::compact(1284503),           // "1.3M"
    'Signups'    => Number::format(48210),              // "48,210"
    'Conversion' => Number::percentage(48210 / 1284503, 2), // "3.75%"
    'Rank'       => Number::ordinal(3),                 // "3rd"
];
```

### 3. Invoices in the customer's locale

The same amount, formatted for each customer, with a deterministic fallback when you don't know their locale.

- ===Invoice

  ```php title="src/Billing/InvoiceLine.php"
  use Pharaonic\Readable\Money;

  final class InvoiceLine
  {
      public function __construct(
          public float $amount,
          public string $currency,
      ) {}

      public function total(?string $locale = null): string
      {
          return Money::format($this->amount, $this->currency, locale: $locale);
      }
  }
  ```

- ===Usage

  ```php title="example.php"
  $line = new InvoiceLine(1234.5, 'EUR');

  $line->total();          // "EUR 1,234.50"
  $line->total('de_DE');   // "1.234,50 €"
  $line->total('en_US');   // "€1,234.50"

  (new InvoiceLine(1234.5, 'JPY'))->total(); // "JPY 1,235"
  ```

### 4. Avatar placeholders

Users without a photo get their initials.

```php title="src/Users/Avatar.php"
use Pharaonic\Readable\Str;

function avatarLabel(string $displayName): string
{
    return Str::initials($displayName) ?: '?';
}

avatarLabel('Moamen Eltouny');  // "ME"
avatarLabel('   ');             // "?"
```

### 5. Upload limits from configuration

Keep limits human-readable in configuration, parse them once, and show them back to the user.

```php title="src/Uploads/Limit.php"
use Pharaonic\Readable\Bytes;

$limit = Bytes::parse(getenv('UPLOAD_LIMIT') ?: '25 MB');   // 25000000

if ($file['size'] > $limit) {
    throw new RuntimeException(sprintf(
        'The file is %s; the limit is %s.',
        Bytes::format($file['size']),   // "31.46 MB"
        Bytes::format($limit),          // "25 MB"
    ));
}
```
