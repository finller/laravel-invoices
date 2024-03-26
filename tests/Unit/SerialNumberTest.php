<?php

use Finller\Invoice\SerialNumberGenerator;

it('can generate serial number from format', function ($format, $prefix, $serie, $count, $expected) {
    $generator = new SerialNumberGenerator(
        format: $format,
    );

    $serialNumber = $generator->generate(
        prefix: $prefix,
        count: $count,
        serie: $serie,
        year: '2022',
        month: '01'
    );

    expect($serialNumber)->toBe($expected);
})->with([
    ['PP-YYCCCC', 'IN', null, 2, 'IN-220002'],
    ['SSSS-YYCCCC', null, 1, 2, '0001-220002'],
    ['SSSS-CCCC', null, 1, 2, '0001-0002'],
    ['SSCC', null, 1, 2, '0102'],
    ['CCCC', null, 1, 2, '0002'],
    ['SSSSPP-YYCCCC', 'IN', 1, 2, '0001IN-220002'],
    ['YYSSSSPPCCCC', 'IN', 1, 2, '220001IN0002'],
    ['YYCCCCSSSSPP', 'IN', 1, 2, '2200020001IN'],
    ['PPSSSS-YYYYCCCC', 'IN', 1, 2, 'IN0001-20220002'],
    ['PPSSSS-YYYCCCC', 'IN', 1, 2, 'IN0001-0220002'],
    ['PPCCCC', 'IN', null, 102, 'IN0102'],
    ['PPSSSS-YYCCCC', 'YC', 1, 2, 'YC0001-220002'],
    ['PPSSSS-YYCCCC', 'PS', 1, 2, 'PS0001-220002'],
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
]);
