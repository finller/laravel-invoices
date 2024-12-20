# Everything You Need to Manage Invoices in Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/finller/laravel-invoices.svg?style=flat-square)](https://packagist.org/packages/finller/laravel-invoices)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/finller/laravel-invoices/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/finller/laravel-invoices/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/finller/laravel-invoices/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/finller/laravel-invoices/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/finller/laravel-invoices.svg?style=flat-square)](https://packagist.org/packages/finller/laravel-invoices)

This package provides a robust, easy-to-use system for managing invoices within a Laravel application, with options for database storage, serial numbering, and PDF generation.

![laravel-invoices](https://repository-images.githubusercontent.com/527661364/030d6384-41e5-454c-a9f5-9f6eebe93da7)

## Demo

Try out [the interactive demo](https://elegantengineering.tech/laravel-invoices) to explore package capabilities.

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
use Finller\Invoice\Invoice;
use Finller\Invoice\InvoiceDiscount;
use Finller\Invoice\InvoiceItem;
use Finller\Invoice\InvoiceType;

return [

    'model_invoice' => Invoice::class,
    'model_invoice_item' => InvoiceItem::class,

    'discount_class' => InvoiceDiscount::class,

    'cascade_invoice_delete_to_invoice_items' => true,

    'serial_number' => [
        /**
         * If true, will generate a serial number on creation
         * If false, you will have to set the serial_number yourself
         */
        'auto_generate' => true,

        /**
         * Define the serial number format used for each invoice type
         *
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
        'format' => [
            InvoiceType::Invoice->value => 'PPYYCCCC',
            InvoiceType::Quote->value => 'PPYYCCCC',
            InvoiceType::Credit->value => 'PPYYCCCC',
            InvoiceType::Proforma->value => 'PPYYCCCC',
        ],

        /**
         * Define the default prefix used for each invoice type
         */
        'prefix' => [
            InvoiceType::Invoice->value => 'IN',
            InvoiceType::Quote->value => 'QO',
            InvoiceType::Credit->value => 'CR',
            InvoiceType::Proforma->value => 'PF',
        ],

    ],

    'date_format' => 'Y-m-d',

    'default_seller' => [
        'name' => null,
        'address' => [
            'street' => null,
            'city' => null,
            'postal_code' => null,
            'state' => null,
            'country' => null,
        ],
        'email' => null,
        'phone_number' => null,
        'tax_number' => null,
        'company_number' => null,
    ],

    /**
     * ISO 4217 currency code
     */
    'default_currency' => 'USD',

    'pdf' => [
        /**
         * Default DOM PDF options
         *
         * @see Available options https://github.com/barryvdh/laravel-dompdf#configuration
         */
        'options' => [
            'isPhpEnabled' => true,
            'fontHeightRatio' => 0.9,
            /**
             * Supported values are: 'DejaVu Sans', 'Helvetica', 'Courier', 'Times', 'Symbol', 'ZapfDingbats'
             */
            'defaultFont' => 'Helvetica',
        ],

        'paper' => [
            'paper' => 'a4',
            'orientation' => 'portrait',
        ],

        /**
         * The logo displayed in the PDF
         */
        'logo' => null,

        /**
         * The color displayed at the top of the PDF
         */
        'color' => '#050038',

        /**
         * The template used to render the PDF
         */
        'template' => 'default.layout',

    ],

];
```

## Store an invoice in your database

You can store an Invoice in your database using the Eloquent Model: `Finller\Invoice\Invoice`.

> [!NOTE]
> Don't forget to publish and run the migrations

Here is a full example:

```php
use Finller\Invoice\Invoice;
use Finller\Invoice\InvoiceState;
use Finller\Invoice\InvoiceType;

// Let's say your app edit invoices for your users
$customer = User::find(1);
// Let's imagine that your users have purchased something in your app
$order = Order::find(2);

$invoice = new Invoice([
    'type' => InvoiceType::Invoice,
    'state' => InvoiceState::Draft,
    'description' => 'A description for my invoice',
    'seller_information' => config('invoices.default_seller'),
    'buyer_information' => [
        'name' => 'John Doe',
        'address' => [
            'street' => '8405 Old James St.Rochester',
            'city' => 'New York',
            'postal_code' => '14609',
            'state' => 'New York (NY)',
            'country' => 'United States',
        ],
        'email' => 'john.doe@example.com',
        'tax_number' => "FR123456789",
    ],
    // ...
]);

$invoice->buyer()->associate($customer); // optionnally associate the invoice to any model
$invoice->invoiceable()->associate($order); // optionnally associate the invoice to any model

$invoice->save();

$invoice->items()->saveMany([
    new InvoiceItem([
        'unit_price' => Money::of(100, 'USD'),
        'unit_tax' => Money::of(20, 'USD'),
        'currency' => 'USD',
        'quantity' => 1,
        'label' => 'A label for my item',
        'description' => 'A description for my item',
    ]),
]);
```

## Automatic Generation of Unique Serial Numbers

This package provides a simple and reliable way to generate serial numbers automatically, such as "INV240001."

You can configure the format of your serial numbers in the configuration file. The default format is `PPYYCCCC`, where each letter has a specific meaning (see the config file for details).

When `invoices.serial_number.auto_generate` is set to `true`, a unique serial number is assigned to each new invoice automatically.

Serial numbers are generated sequentially, with each new serial number based on the latest available one. To define what qualifies as the `previous` serial number, you can extend the `Finller\Invoice\Invoice` class and override the `getPreviousInvoice` method.

By default, the previous invoice is determined based on criteria such as prefix, series, year, and month for accurate, scoped numbering.

## Managing Multiple Prefixes and Series

In more complex applications, you may need to use different prefixes and/or series for your invoices.

For instance, you might want to define a unique series for each user, creating serial numbers that look like: `INV0001-2400X`, where `0001` represents the user’s ID, `24` the year and `X` the index of the invoice.

> [!NOTE]
> When using IDs for series, it's recommended to plan for future growth to avoid overflow.
> Even if you have a limited number of users now, ensure that the ID can accommodate the maximum number of digits allowed by the serial number format.

When creating an invoice, you can dynamically specify the prefix and series as follows:

```php
use Finller\Invoice\Invoice;
$invoice = new Invoice();

$invoice->configureSerialNumber(
    prefix: "ORG",
    serie: $buyer_id,
);
```

## Customizing the Serial Number Format

In most cases, the format of your serial numbers should remain consistent, so it's recommended to set it in the configuration file.

The format you choose will determine the types of information you need to provide to `configureSerialNumber`.

Below is an example of the most complex serial number format you can create with this package:

```php

$invoice = new Invoice();

$invoice->configureSerialNumber(
    format: "PP-SSSSSS-YYMMCCCC",
    prefix: "IN",
    serie: 100,
    year: now()->format('Y'),
    month: now()->format('m')
);

$invoice->save();

$invoice->serial_number; // IN-000100-2410-0001
```

## Display your invoice as a PDF

The Invoice model has a `toPdfInvoice()` that return a `PdfInvoice` class.

### As a response in a controller

You can stream the `pdfInvoice` instance as a response, or download it:

```php
namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function show(Request $request, string $serial)
    {
        /** @var Invoice $invoice */
        $invoice = Invoice::where('serial_number', $serial)->firstOrFail();

        $this->authorize('view', $invoice);

        return $invoice->toPdfInvoice()->stream();
    }

    public function download(Request $request, string $serial)
    {
        /** @var Invoice $invoice */
        $invoice = Invoice::where('serial_number', $serial)->firstOrFail();

        $this->authorize('view', $invoice);

        return $invoice->toPdfInvoice()->download();
    }
}
```

### As a mail Attachment

The `Invoice` model provide a `toMailAttachment` method making it easy to use with `Mailable`

```php
namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentInvoice extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        protected Invoice $invoice,
    ) {}


    public function attachments(): array
    {
        return [
            $this->invoice->toMailAttachment()
        ];
    }
}
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

-   [Quentin Gabriele](https://github.com/QuentinGab)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
