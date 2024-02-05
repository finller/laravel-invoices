<?php

use Finller\Invoice\Invoice;
use Finller\Invoice\InvoiceItem;
use Finller\Invoice\InvoiceType;

it('can create and generate unique serial numbers', function () {
    $prefix = 'INV';
    config()->set('invoices.serial_number.format', 'PPPYYCCCC');
    config()->set('invoices.serial_number.prefix', $prefix);

    $year = now()->format('y');

    /** @var Invoice */
    $invoice = Invoice::factory()->create();
    /** @var Invoice */
    $invoice2 = Invoice::factory()->create();

    expect($invoice->serial_number)->toBe("{$prefix}{$year}0001");
    expect($invoice->serial_number_details)->toMatchArray([
        'prefix' => $prefix,
        'serie' => null,
        'month' => null,
        'year' => intval($year),
        'count' => 1,
    ]);

    expect($invoice2->serial_number)->toBe("{$prefix}{$year}0002");

    expect((new Invoice())->generateSerialNumber())->toBe("{$prefix}{$year}0003");
});

it('can create and generate unique serial numbers based on invoice type', function () {
    config()->set('invoices.serial_number.format', [
        InvoiceType::Invoice->value => 'PPPYYC',
        InvoiceType::Quote->value => 'PPPYYCC',
        InvoiceType::Credit->value => 'PPPYYCCC',
        InvoiceType::Proforma->value => 'PPPYYCCCC',
    ]);

    config()->set('invoices.serial_number.prefix', [
        InvoiceType::Invoice->value => 'INV',
        InvoiceType::Quote->value => 'QUO',
        InvoiceType::Credit->value => 'CRE',
        InvoiceType::Proforma->value => 'PRO',
    ]);

    $year = now()->format('y');

    /** @var Invoice */
    $invoice = Invoice::factory()->invoice()->create();
    /** @var Invoice */
    $quote = Invoice::factory()->quote()->create();
    /** @var Invoice */
    $credit = Invoice::factory()->credit()->create();
    /** @var Invoice */
    $proforma = Invoice::factory()->proforma()->create();

    expect($invoice->serial_number)->toBe("INV{$year}1");
    expect($quote->serial_number)->toBe("QUO{$year}01");
    expect($credit->serial_number)->toBe("CRE{$year}001");
    expect($proforma->serial_number)->toBe("PRO{$year}0001");

    expect($invoice->serial_number_details)->toMatchArray([
        'prefix' => 'INV',
        'serie' => null,
        'month' => null,
        'year' => intval($year),
        'count' => 1,
    ]);

});

it('can create serial number with serie defined on the fly', function () {
    $prefix = 'IN';
    config()->set('invoices.serial_number.format', 'PPSSSS-YYCCCC');
    config()->set('invoices.serial_number.prefix', $prefix);
    $year = now()->format('y');

    /** @var Invoice */
    $invoice = Invoice::factory()->make();

    $invoice->setSerialNumberSerie(42);

    expect($invoice->getSerialNumberSerie())->toBe(42);

    $invoice->save();

    expect($invoice->serial_number)->toBe("{$prefix}0042-{$year}0001");
});

it('can create serial number with prefix defined on the fly', function () {
    config()->set('invoices.serial_number.format', 'PPPSSSS-YYCCCC');

    $year = now()->format('y');

    /** @var Invoice */
    $invoice = Invoice::factory()->make();

    $invoice->setSerialNumberSerie(42);
    $invoice->setSerialNumberPrefix('ORG');

    expect($invoice->getSerialNumberSerie())->toBe(42);
    expect($invoice->getSerialNumberPrefix())->toBe('ORG');

    $invoice->save();

    expect($invoice->serial_number)->toBe("ORG0042-{$year}0001");

    expect($invoice->serial_number_details)->toMatchArray([
        'prefix' => 'ORG',
        'serie' => 42,
        'month' => null,
        'year' => intval($year),
        'count' => 1,
    ]);
});

it('denormalize amounts in invoice', function () {
    /** @var Invoice */
    $invoice = Invoice::factory()->make();
    $invoice->save();

    $invoice->items()->saveMany(InvoiceItem::factory(2)->make());

    $invoice->denormalize()->save();
    $pdfInvoice = $invoice->toPdfInvoice();

    expect($invoice->subtotal_amount->getAmount()->toFloat())
        ->toEqual($pdfInvoice->subTotalAmount()->getAmount()->toFloat());

    expect($invoice->discount_amount->getAmount()->toFloat())
        ->toEqual($pdfInvoice->totalDiscountAmount()->getAmount()->toFloat());

    expect($invoice->tax_amount->getAmount()->toFloat())
        ->toEqual($pdfInvoice->totalTaxAmount()->getAmount()->toFloat());

    expect($invoice->total_amount->getAmount()->toFloat())
        ->toEqual($pdfInvoice->totalAmount()->getAmount()->toFloat());
});
