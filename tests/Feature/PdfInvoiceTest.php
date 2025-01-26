<?php

declare(strict_types=1);

use Brick\Money\Money;
use Finller\Invoice\PdfInvoice;
use Finller\Invoice\PdfInvoiceItem;

it('computes the right subTotalAmount, totalTaxAmount and totalAmount', function () {
    $pdfInvoice = new PdfInvoice(
        name: 'Invoice',
        serial_number: 'FAKE-INVOICE-01',
        state: 'paid',
        due_at: now(),
        created_at: now(),
        buyer: config('invoices.default_seller')
    );

    $pdfInvoice->items = [
        new PdfInvoiceItem(
            label: 'Item 1',
            unit_price: Money::of(110, 'USD'),
            unit_tax: Money::of(10, 'USD')
        ),
        new PdfInvoiceItem(
            label: 'Item 1',
            unit_price: Money::of(234, 'USD'),
            unit_tax: Money::of(12, 'USD')
        ),
    ];

    expect($pdfInvoice->subTotalAmount()->getAmount()->toFloat())->toEqual(344);
    expect($pdfInvoice->totalTaxAmount()->getAmount()->toFloat())->toEqual(22);
    expect($pdfInvoice->totalAmount()->getAmount()->toFloat())->toEqual(366);
});
