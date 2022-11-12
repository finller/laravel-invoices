<?php

namespace Finller\Invoice;

use Carbon\Carbon;

interface GenerateSerialNumber
{

    public function generate(?int $serie = null, ?Carbon $date = null, int $count): string;

    public function parse(string $serialNumber): array;
}
