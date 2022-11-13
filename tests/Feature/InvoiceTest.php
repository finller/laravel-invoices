<?php

use Finller\Invoice\Invoice;

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
    expect($invoice2->serial_number)->toBe("{$prefix}{$year}0002");

    expect(Invoice::generateSerialNumber())->toBe("{$prefix}{$year}0003");
});
