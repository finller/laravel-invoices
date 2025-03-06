<?php

declare(strict_types=1);

namespace Elegantly\Invoices\Contracts;

interface GenerateSerialNumber
{
    public function __construct(string $format);

    public function generate(
        int $count,
        string|int|null $prefix = null,
        string|int|null $serie = null,
        string|int|null $year = null,
        string|int|null $month = null,
    ): string;

    /**
     * @return array{
     *      prefix: ?string,
     *      serie: ?int,
     *      month: ?int,
     *      year: ?int,
     *      count: ?int,
     * }
     */
    public function parse(string $serialNumber): array;
}
