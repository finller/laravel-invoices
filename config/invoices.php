<?php

// config for Finller/Invoice

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
