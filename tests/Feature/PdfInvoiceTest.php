<?php

declare(strict_types=1);

use Brick\Money\Money;
use Elegantly\Invoices\Enums\InvoiceState;
use Elegantly\Invoices\Enums\InvoiceType;
use Elegantly\Invoices\Pdf\PdfInvoice;
use Elegantly\Invoices\Pdf\PdfInvoiceItem;

it('computes the right subTotalAmount, totalTaxAmount and totalAmount', function () {
    $pdfInvoice = new PdfInvoice(
        type: InvoiceType::Invoice,
        state: InvoiceState::Paid,
        serial_number: 'FAKE-INVOICE-01',
        due_at: now(),
        created_at: now(),
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
