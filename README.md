<p align="center"><a href="https://pharaonic.dev/packages/php/readable" target="_blank"><img src="https://raw.githubusercontent.com/Pharaonic/php-readable/develop/docs/cover.webp"></a></p>

<p align="center">
  <a href="https://php.net" target="_blank"><img src="https://img.shields.io/static/v1?label=PHP&message=8.3&color=blue&style=flat-square" alt="PHP Version : 8.3"></a>
  <img src="https://img.shields.io/static/v1?label=License&message=MIT&color=brightgreen&style=flat-square" alt="License">
  <img src="https://github.com/Pharaonic/php-readable/actions/workflows/build.yml/badge.svg" alt="Tests">
  <br>
  <a href="https://packagist.org/packages/Pharaonic/php-readable" target="_blank"><img src="https://img.shields.io/static/v1?label=Packagist&message=pharaonic/php-readable&color=blue&logo=packagist&logoColor=white" alt="Source"></a>
  <a href="https://packagist.org/packages/pharaonic/php-readable" target="_blank"><img src="https://poser.pugx.org/pharaonic/php-readable/v" alt="Packagist Version"></a>
  <a href="https://packagist.org/packages/pharaonic/php-readable" target="_blank"><img src="https://poser.pugx.org/pharaonic/php-readable/downloads" alt="Packagist Downloads"></a>
</p>

<h3 align="center">Human-friendly numbers, sizes, money and durations for PHP.</h3>
<h5 align="center">Format numbers, byte sizes, money and durations into readable text with predictable output, plus optional Intl locales.</h5>
<br>

## Installation

```bash
composer require pharaonic/php-readable
```

## Usage

```php
use Pharaonic\Readable\{Bytes, Duration, Money, Number};

Number::compact(1250000);           // "1.3M"
Bytes::format(1536, binary: true);  // "1.5 KiB"
Money::format(1234.5, 'USD');       // "USD 1,234.50"
Duration::format(3661);             // "1 hour 1 minute 1 second"
```

## Documentation

Full documentation: [PHP Readable Documentation](https://pharaonic.dev/packages/php/readable).

## Support & Security

See [SUPPORT.md](SUPPORT.md) and [SECURITY.md](SECURITY.md). Contributions are welcome; see [CONTRIBUTING.md](CONTRIBUTING.md).

## License

MIT. See [LICENSE](LICENSE).
