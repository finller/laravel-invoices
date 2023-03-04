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
        $this->prefix = $prefix ?? config('invoices.serial_number.prefix', '');
    }

    public function generate(int $count, ?int $serie = null, ?Carbon $date = null): string
    {
        return preg_replace_callback_array(
            [
                '/S+/' => function ($matches) use ($serie) {
                    if (! $matches[0]) {
                        return '';
                    }
                    $slotLength = strlen($matches[0]);
                    throw_if(! $serie, "The serial Number format includes a $slotLength long Serie (S), but no serie has been passed");

                    $serieLength = strlen(strval($serie));
                    throw_if(
                        $serieLength > $slotLength,
                        "The Serial Number can't be formatted: Serie ($serie) is ($serieLength) digits long while the format has only $slotLength slots."
                    );

                    return str_pad(
                        $serie,
                        $slotLength,
                        '0',
                        STR_PAD_LEFT
                    );
                },
                '/M+/' => fn ($matches) => $matches[0] && $date ? substr($date->format('m'), -strlen($matches[0])) : '',
                '/Y+/' => fn ($matches) => $matches[0] && $date ? substr($date->format('Y'), -strlen($matches[0])) : '',
                '/C+/' => function ($matches) use ($count) {
                    if (! $matches[0]) {
                        return '';
                    }
                    throw_if(
                        ($countLength = strlen(strval($count))) > $slotLength = strlen($matches[0]),
                        "The Serial Number can't be formatted: Count ($count) is ($countLength) digit long while the format has only $slotLength slots."
                    );

                    return str_pad(
                        $count,
                        $slotLength,
                        '0',
                        STR_PAD_LEFT
                    );
                },
                // Must be kept last to avoid interfering with other callbacks
                '/P+/' => function ($matches) {
                    if (! $matches[0]) {
                        return '';
                    }
                    $slotLength = strlen($matches[0]);
                    $prefixLength = strlen($this->prefix);

                    throw_if(
                        $prefixLength < $slotLength,
                        "The serial Number can't be formatted, the prefix provided is $prefixLength letters long ({$this->prefix}), while the format require at minimum a $slotLength letters long prefix"
                    );

                    return substr($this->prefix, 0, strlen($matches[0]));
                },
            ],
            $this->format
        );
    }

    public function parse(string $serialNumber): array
    {
        preg_match("/{$this->formatToRegex()}/", $serialNumber, $matches);

        return [
            'prefix' => data_get($matches, 'prefix'),
            'serie' => ($serie = data_get($matches, 'serie')) ? intval($serie) : null,
            'month' => ($month = data_get($matches, 'month')) ? intval($month) : null,
            'year' => ($year = data_get($matches, 'year')) ? intval($year) : null,
            'count' => ($count = data_get($matches, 'count')) ? intval($count) : null,
        ];
    }

    protected function formatToRegex(): string
    {
        return preg_replace_callback_array(
            [
                '/P+/' => fn ($matches) => ($matches[0] && $length = strlen($matches[0])) ? "(?<prefix>[a-zA-Z]{{$length}})" : '',
                '/S+/' => fn ($matches) => ($matches[0] && $length = strlen($matches[0])) ? "(?<serie>\d{{$length}})" : '',
                '/M+/' => fn ($matches) => ($matches[0] && $length = strlen($matches[0])) ? "(?<month>\d{{$length}})" : '',
                '/Y+/' => fn ($matches) => ($matches[0] && $length = strlen($matches[0])) ? "(?<year>\d{{$length}})" : '',
                '/C+/' => fn ($matches) => ($matches[0] && $length = strlen($matches[0])) ? "(?<count>\d{{$length}})" : '',
            ],
            $this->format
        );
    }
}
