<?php

namespace Finller\Invoice;

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

    public function parse(string $serialNumber): array;
}
