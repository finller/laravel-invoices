<?php

declare(strict_types=1);

use Finller\Invoice\SerialNumberGenerator;

it('can generate serial number from format', function ($format, $prefix, $serie, $year, $month, $count, $expected) {
    $generator = new SerialNumberGenerator(
        format: $format,
    );

    $serialNumber = $generator->generate(
        count: $count,
        prefix: $prefix,
        serie: $serie,
        year: $year,
        month: $month
    );

    expect($serialNumber)->toBe($expected);
})->with([
    ['PPPSSSSSSS-YYMMCCCC', 'INV', 1, 2025, 1, 1, 'INV0000001-25010001'],
    ['PPPSSSSSSS-YYMMCCCC', 'INV', 1, 25, 1, 1, 'INV0000001-25010001'],
    ['PPPSSSSSSS-YYMMCCCC', 'INV', 1000001, 25, 12, 1001, 'INV1000001-25121001'],
    ['PPP-YYMMCCCC', 'INV', null, 25, 12, 1001, 'INV-25121001'],
    ['PPP-MMCCCC', 'INV', null, null, 12, 1001, 'INV-121001'],
    ['PPP-CCCC', 'INV', null, null, null, 1001, 'INV-1001'],
    ['CCCC', null, null, null, null, 1001, '1001'],
    ['PPPSSSSSSS-CCCC', 'INV', 1000001, null, null, 1001, 'INV1000001-1001'],
    ['PPPSSSSSSS-YYCCCC', 'INV', 1000001, 25, null, 1001, 'INV1000001-251001'],
    ['PPP-YYCCCC', 'INV', 1000001, 25, null, 1001, 'INV-251001'],
]);

it('can parse serial number from format', function ($format, $serialNumber, $prefix, $serie, $year, $count) {
    $generator = new SerialNumberGenerator(
        format: $format,
    );

    $serialNumberParsed = $generator->parse($serialNumber);

    expect($serialNumberParsed)->toMatchArray([
        'prefix' => $prefix,
        'serie' => $serie,
        'year' => $year,
        'count' => $count,
    ]);
})->with([
    ['PPSSSS-YYCCCC', 'IN0001-220002', 'IN', 1, 22, 2],
    ['SSSS-YYCCCC', '0001-220002', null, 1, 22, 2],
    ['SSSSYYCCCC', '0001220002', null, 1, 22, 2],
    ['SSYYCCCC', '01220002', null, 1, 22, 2],
    ['YYCCCC', '220002', null, null, 22, 2],
    ['YYPPCCCSSSS', '22IN0020001', 'IN', 1, 22, 2],
    ['YYPPCCCSSSS', '22CS0020001', 'CS', 1, 22, 2],
    ['YYPPCCC/SSSS', '22CS002/0001', 'CS', 1, 22, 2],
    ['CCCC/YY', '0002/25', null, null, 25, 2],
]);
