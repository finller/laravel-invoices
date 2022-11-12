<?php

namespace Finller\Invoice;

use Carbon\Carbon;

class SerialNumberGenerator implements GenerateSerialNumber
{
    public function __construct(
        public ?string $format = null,
        public ?string $prefix = null,
    ) {
        $this->format = $format ?? config('invoices.serial_number.format', '');
        $this->prefix = $prefix ?? config('invoices.serial_number.default_prefix', '');
    }

    public function generate(?int $serie, ?Carbon $date, int $count): string
    {
        return preg_replace_callback_array(
            [
                '/P+/' => fn ($matches) => $matches[0] ? substr($this->prefix, 0, strlen($matches[0])) : '',
                '/S+/' => function ($matches) use ($serie) {
                    if (! $matches[0] || ! $serie) {
                        return '';
                    }
                    throw_if(
                        $serieLength = strlen(strval($serie)) > $slotLength = strlen($matches[0]),
                        "The Serial Number can't be formatted: Serie ($serie) is ($serieLength) digit long while the format has only $slotLength slots."
                    );

                    return str_pad(
                        $serie,
                        $slotLength,
                        '0',
                        STR_PAD_LEFT
                    );
                },
                '/Y+/' => fn ($matches) => $matches[0] && $date ? substr($date->format('Y'), -strlen($matches[0])) : '',
                '/C+/' => function ($matches) use ($count) {
                    if (! $matches[0]) {
                        return '';
                    }
                    throw_if(
                        $countLength = strlen(strval($count)) > $slotLength = strlen($matches[0]),
                        "The Serial Number can't be formatted: Count ($count) is ($countLength) digit long while the format has only $slotLength slots."
                    );

                    return str_pad(
                        $count,
                        $slotLength,
                        '0',
                        STR_PAD_LEFT
                    );
                },
            ],
            $this->format
        );
    }

    public function parse(string $serialNumber): array
    {
        return [];
    }
}
