<?php

namespace Finller\Invoice;

use Carbon\Carbon;

interface GenerateSerialNumber
{
    public function __construct(?string $format = null, ?string $prefix = null);

    public function generate(int $count, ?int $serie = null, ?Carbon $date = null): string;

    public function parse(string $serialNumber): array;
}
