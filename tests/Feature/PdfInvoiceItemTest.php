<?php

declare(strict_types=1);

use Brick\Money\Money;
use Elegantly\Invoices\Pdf\PdfInvoiceItem;

it('computes the right subTotalAmount', function ($unit_price, $quantity, $unit_tax, $expected) {
    $item = new PdfInvoiceItem(
        label: 'Item 1',
        unit_price: Money::of($unit_price, 'USD'),
        quantity: $quantity,
        unit_tax: Money::of($unit_tax, 'USD')
    );

    expect($item->subTotalAmount()->getAmount()->toFloat())->toEqual($expected);
})->with([
    [110, 3, 10, 330],
    [1567, 3, 209, 4701],
    [578, 0, 468, 0],
]);

it('computes the right totalTaxAmount with unit_tax', function ($unit_price, $quantity, $unit_tax, $expected) {
    $item = new PdfInvoiceItem(
        label: 'Item 1',
        unit_price: Money::of($unit_price, 'USD'),
        quantity: $quantity,
        unit_tax: Money::of($unit_tax, 'USD')
    );

    expect($item->totalTaxAmount()->getAmount()->toFloat())->toEqual($expected);
})->with([
    [110, 3, 10, 30],
    [1567, 3, 209, 627],
    [578, 0, 468, 0],
    [0, 10, 468, 4680],
]);

it('computes the right totalTaxAmount with tax_percentage', function ($unit_price, $quantity, $tax_percentage, $expected) {
    $item = new PdfInvoiceItem(
        label: 'Item 1',
        unit_price: Money::of($unit_price, 'USD'),
        quantity: $quantity,
        tax_percentage: $tax_percentage
    );

    expect($item->totalTaxAmount()->getAmount()->toFloat())->toEqual($expected);
})->with([
    [110, 3, 20, 66],
    [1567, 3, 10, 470.1],
    [578, 0, 30, 0],
    [0, 10, 20, 0],
    [6789, 10, 0, 0],
]);

it('computes the right totalTaxAmount with unit_tax when both unit_tax and tax_percentage are defined', function ($unit_price, $quantity, $unit_tax, $tax_percentage, $expected) {
    $item = new PdfInvoiceItem(
        label: 'Item 1',
        unit_price: Money::of($unit_price, 'USD'),
        quantity: $quantity,
        tax_percentage: $tax_percentage,
        unit_tax: Money::of($unit_tax, 'USD')
    );

    expect($item->totalTaxAmount()->getAmount()->toFloat())->toEqual($expected);
})->with([
    [110, 3, 100, 20, 300],
    [1567, 3, 30, 10, 90],
    [578, 0, 10, 30, 0],
    [0, 10, 50, 20, 500],
    [6789, 10, 10, 0, 100],
    [938, 10, 0, 20, 0],
]);
