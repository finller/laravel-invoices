
# Manage invoices in your Laravel App

[![Latest Version on Packagist](https://img.shields.io/packagist/v/finller/laravel-invoices.svg?style=flat-square)](https://packagist.org/packages/finller/laravel-invoices)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/finller/laravel-invoices/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/finller/laravel-invoices/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/finller/laravel-invoices/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/finller/laravel-invoices/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/finller/laravel-invoices.svg?style=flat-square)](https://packagist.org/packages/finller/laravel-invoices)

Creating invoices is not a basic operation as you must ensure that it's done safely.
This package provid all the basics to create and stores invoices in your app.

## Installation

You can install the package via composer:

```bash
composer require finller/laravel-invoices
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="invoices-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="invoices-config"
```

This is the contents of the published config file:

```php
return [

    'model_invoice_item' => InvoiceItem::class,
    'model_invoice' => Invoice::class,

    'cascade_invoice_delete_to_invoice_items' => true,

    /**
     * This is the class that will used to cast all amount and prices
     * We recommand to use solution such as:
     *
     * @see https://github.com/brick/money or https://github.com/akaunting/laravel-money
     */
    'money_cast' => null,

    'serial_number' => [
        /**
         * If true, will generate a serial number on creation
         * If false, you will have to set the serial_number yourself
         */
        'auto_generate' => true,

        /**
         * P: Prefix
         * S: Serie
         * M: Month
         * Y: Year
         * C: Count
         * Example: IN0012-220234
         * Repeat letter to set the length of each information
         * Examples of formats:
         * - PPYYCCCC : IN220123 (default)
         * - PPPYYCCCC : INV220123
         * - PPSSSS-YYCCCC : INV0001-220123
         * - SSSS-CCCC: 0001-0123
         * - YYCCCC: 220123
         */
        'format' => 'PPYYCCCC',

        'prefix' => 'IN',

    ],
];
```

## Usage

```php
use Finller\Invoice\Invoice;

$invoice = new Invoice();
$invoice->save();
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
