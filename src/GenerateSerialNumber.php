<?php

namespace Finller\Invoice;

interface GenerateSerialNumber
{
    public function __construct(string $format = null, string $prefix = null);

    public function generate(
        int $count,
        int $serie = null,
        string|int $year = null,
        string|int $month = null
    ): string;

    public function parse(string $serialNumber): array;
}
