<?php

// config for Finller/Invoice

use Finller\Invoice\Invoice;
use Finller\Invoice\InvoiceItem;

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
