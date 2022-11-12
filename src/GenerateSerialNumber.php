<?php

namespace Finller\Invoice;

use Carbon\Carbon;

interface GenerateSerialNumber
{
    public function __construct(?string $format = null, ?string $prefix = null);

    public function generate(?int $serie, ?Carbon $date, int $count): string;

    public function parse(string $serialNumber): array;
}
