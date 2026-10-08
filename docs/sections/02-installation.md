## Installation

Install the package with Composer. There is nothing to register, publish or configure.

### Requirements

- PHP 8.0.x (each `8.x` release line targets the matching PHP version)
- `ext-mbstring`
- `ext-intl` *(optional)*: only needed when you pass a `$locale`

### Composer Installation

```bash title="Terminal" no-line-numbers
composer require pharaonic/php-readable
```

Composer autoloads the `Pharaonic\Readable` namespace. The package ships no global functions.

:::success Installation Complete
You're all set! Try `Number::compact(1250000)`, which returns `1.3M`.
:::
