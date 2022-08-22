
[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/support-ukraine.svg?t=1" />](https://supportukrainenow.org)

# This is my package laravel-invoices

[![Latest Version on Packagist](https://img.shields.io/packagist/v/finller/laravel-invoices.svg?style=flat-square)](https://packagist.org/packages/finller/laravel-invoices)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/finller/laravel-invoices/run-tests?label=tests)](https://github.com/finller/laravel-invoices/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/finller/laravel-invoices/Fix%20PHP%20code%20style%20issues?label=code%20style)](https://github.com/finller/laravel-invoices/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/finller/laravel-invoices.svg?style=flat-square)](https://packagist.org/packages/finller/laravel-invoices)

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Installation

You can install the package via composer:

```bash
composer require finller/laravel-invoices
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="laravel-invoices-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-invoices-config"
```

This is the contents of the published config file:

```php
return [
];
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="laravel-invoices-views"
```

## Usage

```php
$invoice = new Finller\Invoice();
echo $invoice->echoPhrase('Hello, Finller!');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Quentin Gabriele](https://github.com/QuentinGab)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
