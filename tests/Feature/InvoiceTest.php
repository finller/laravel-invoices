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

it('can create serial number with serie defined on the fly', function () {
    $prefix = 'IN';
    config()->set('invoices.serial_number.format', 'PPSSSS-YYCCCC');
    config()->set('invoices.serial_number.prefix', $prefix);
    $year = now()->format('y');

    /** @var Invoice */
    $invoice = Invoice::factory()->make();

    $invoice->serie = 42;

    expect($invoice->getSerialNumberSerie())->toBe(42);

    $invoice->save();

    expect($invoice->serial_number)->toBe("{$prefix}0042-{$year}0001");
});

it('can create serial number with prefix defined on the fly', function () {
    config()->set('invoices.serial_number.format', 'PPPSSSS-YYCCCC');

    $year = now()->format('y');

    /** @var Invoice */
    $invoice = Invoice::factory()->make();

    $invoice->serie = 42;
    $invoice->prefix = 'ORG';

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
