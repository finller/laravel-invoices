<?php

declare(strict_types=1);

use Elegantly\Invoices\SerialNumberGenerator;

$cases = [
    // $format, $serialNumber, $prefix, $serie, $year, $month, $count
    ['PPPSSSSSSS-YYMMCCCC', 'INV0000001-25010001', 'INV', 1, 25, 1, 1],
    ['PPPSSSSSSS-YYMMCCCC', 'INV0000001-25010001', 'INV', 1, 25, 1, 1],
    ['PPPSSSSSSS-YYMMCCCC', 'INV1000001-25121001', 'INV', 1000001, 25, 12, 1001],
    ['PPP-YYMMCCCC', 'INV-25121001', 'INV', null, 25, 12, 1001],
    ['PPP-MMCCCC', 'INV-121001', 'INV', null, null, 12, 1001],
    ['PPP-CCCC', 'INV-1001', 'INV', null, null, null, 1001],
    ['CCCC', '1001', null, null, null, null, 1001],
    ['PPPSSSSSSS-CCCC', 'INV1000001-1001', 'INV', 1000001, null, null, 1001],
    ['PPPSSSSSSS-YYCCCC', 'INV1000001-251001', 'INV', 1000001, 25, null, 1001],
    ['PPP-YYCCCC', 'INV-251001', 'INV', null, 25, null, 1001],
    ['YYPPCCC/SSSS', '22CS002/0001', 'CS', 1, 22, null, 2],
    ['CCCC/YY', '0002/25', null, null, 25, null, 2],
    ['CCCC\YY', '0002\25', null, null, 25, null, 2],
    ['CCCC.YY', '0002.25', null, null, 25, null, 2],
    ['CCCC|YY', '0002|25', null, null, 25, null, 2],
    ['PPP-SSS..CCCC/YY', 'INV-999..0001/25', 'INV', 999, 25, null, 1],
];

it('can generate serial number from format', function ($format, $serialNumber, $prefix, $serie, $year, $month, $count) {
    $generator = new SerialNumberGenerator($format);

    $serialNumber = $generator->generate(
        count: $count,
        prefix: $prefix,
        serie: $serie,
        year: $year,
        month: $month
    );

    expect($serialNumber)->toBe($serialNumber);
})->with([
    ...$cases,
    ['CCCC', '1001', 'INV', 999, 2025, 12, 1001],
]);

it('can parse serial number from format', function ($format, $serialNumber, $prefix, $serie, $year, $month, $count) {
    $generator = new SerialNumberGenerator($format);

    $serialNumberParsed = $generator->parse($serialNumber);

    expect($serialNumberParsed)->toMatchArray([
        'prefix' => $prefix,
        'serie' => $serie,
        'year' => $year,
        'month' => $month,
        'count' => $count,
    ]);
})->with($cases);
