# Manage invoices in your Laravel App

[![Latest Version on Packagist](https://img.shields.io/packagist/v/finller/laravel-invoices.svg?style=flat-square)](https://packagist.org/packages/finller/laravel-invoices)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/finller/laravel-invoices/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/finller/laravel-invoices/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/finller/laravel-invoices/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/finller/laravel-invoices/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/finller/laravel-invoices.svg?style=flat-square)](https://packagist.org/packages/finller/laravel-invoices)

Creating invoices is not a basic operation as you must ensure that it's done safely.
This package provid all the basics to store invoices in your app and display them in a PDF.

## Migrating to v3

The v3 introduce a more robust way to configure serial numbers.

-   `configureSerialNumber` replaces 'setSerialNumberPrefix`, 'setSerialNumberSerie`, ...

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

    'default_logo' => null,

    'default_template' => 'default',

    /**
     * ISO 4217 currency code
     */
    'default_currency' => 'USD',

    /**
     * Default DOM PDF options
     *
     * @see Available options https://github.com/barryvdh/laravel-dompdf#configuration
     */
    'pdf_options' => [],
    'paper_options' => [
        'paper' => 'a4',
        'orientation' => 'portrait',
    ],
];
```

## Usage

## Store an invoice in your database

An invoice is just a model with InvoiceItem relationships, so you can create an invoice just like that:

```php
use Finller\Invoice\Invoice;
use Finller\Invoice\InvoiceState;
use Finller\Invoice\InvoiceType;

// Define general invoice data
$invoice = new Invoice([
    'type' => InvoiceType::Invoice,
    'state' => InvoiceState::Draft,
    'description' => 'A description for my invoice',
    'seller_information' => config('invoices.default_seller'),
    'buyer_information' => [
        'name' => 'Client name',
        'address' => [],
        'tax_number' => "XYZ",
    ],
    // ...
]);

$invoice->configureSerialNumber(
    prefix: "CLI",
    serie: 42,
);

$invoice->buyer()->associate($customer);
$invoice->invoiceable()->associate($order); // optionnally associate the invoice to a model

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

## Generate unique serial numbers automatically

This package provid an easy way to generate explicite serial number like "INV-0001" in a safe and automatic way.

You can configure the format of your serial numbers in the config file. The default format is `PPYYCCCC` (see config to understand the meaning of each letters).

Each time you create a new invoice, and if `invoices.serial_number.auto_generate` is set to `true`, the invoice will be given a unique serial number.

Serial number are generated one after the other, the new generated serial number is based on the `previous` one available.
To determine which is the `previous` serial number you can extends `Finller\Invoice\Invoice`
and override the `getPreviousInvoice` method.

By default the previous invoice is scoped by prefix, serie, year and month as you would expect.

## Managing multiple prefix and multiple series

In more complex app, you might need to have different prefix and/or series for your invoices.

For example, you might want to define a serie for each of your user and having serial numbers looking like: INV0001-00X where 1 is the id of the user.

When creating an invoice, you can define the prefix and the serie on the fly like that;

```php
use Finller\Invoice\Invoice;
$invoice = new Invoice();

$invoice->configureSerialNumber(
    prefix: "ORG",
    serie: $buyer_id,
);

$invoice->save();
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
