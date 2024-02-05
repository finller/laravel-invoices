<?php

namespace Finller\Invoice;

interface GenerateSerialNumber
{
    public function __construct(string $format, ?string $prefix = null);

    public function generate(
        int $count,
        ?int $serie = null,
        string|int|null $year = null,
        string|int|null $month = null
    ): string;

    public function parse(string $serialNumber): array;
}
