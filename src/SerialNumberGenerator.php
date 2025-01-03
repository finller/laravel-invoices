<?php

namespace Finller\Invoice;

class SerialNumberGenerator implements GenerateSerialNumber
{
    public function __construct(
        public string $format,
    ) {
        //
    }

    public function generate(
        int $count,
        string|int|null $prefix = null,
        string|int|null $serie = null,
        string|int|null $year = null,
        string|int|null $month = null,
    ): string {
        return preg_replace_callback_array(
            [
                '/S+/' => function ($matches) use ($serie) {
                    $slot = $matches[0] ?? '';
                    $slotLength = strlen($slot);
                    $valueLength = strlen($serie);

                    if ($slotLength < 1) {
                        return '';
                    }

                    if (! $serie) {
                        throw new \Exception("The serial Number format includes a $slotLength long Serie (S), but no serie has been specified.");
                    }

                    if ($valueLength > $slotLength) {
                        throw new \Exception("The Serial Number can't be formatted: Serie ($serie) is $valueLength digits long while the format has only $slotLength slots.");
                    }

                    return str_pad(
                        (string) $serie,
                        $slotLength,
                        '0',
                        STR_PAD_LEFT
                    );
                },
                '/M+/' => function ($matches) use ($month) {
                    $slot = $matches[0] ?? '';
                    $slotLength = strlen($slot);

                    if ($slotLength < 1) {
                        return '';
                    }

                    if (! $month) {
                        throw new \Exception("The serial Number format includes a $slotLength long Month (M), but no month has been specified.");
                    }

                    return str_pad(
                        (string) substr($month, -$slotLength),
                        $slotLength,
                        '0',
                        STR_PAD_LEFT
                    );
                },
                '/Y+/' => function ($matches) use ($year) {
                    $slot = $matches[0] ?? '';
                    $slotLength = strlen($slot);

                    if ($slotLength < 1) {
                        return '';
                    }

                    if (! $year) {
                        throw new \Exception("The serial Number format includes a $slotLength long Year (Y), but no year has been specified.");
                    }

                    return str_pad(
                        (string) substr($year, -$slotLength),
                        $slotLength,
                        '0',
                        STR_PAD_LEFT
                    );
                },
                '/C+/' => function ($matches) use ($count) {
                    $slot = $matches[0] ?? '';
                    $slotLength = strlen($slot);
                    $valueLength = strlen((string) $count);

                    if ($slotLength < 1) {
                        return '';
                    }

                    if (! $count) {
                        throw new \Exception("The serial Number format includes a {$slotLength} long Count (C), but no count has been specified.");
                    }

                    if ($valueLength > $slotLength) {
                        throw new \Exception("The Serial Number can't be formatted: Count ({$count}) is {$valueLength} digits long while the format has only $slotLength slots.");
                    }

                    return str_pad(
                        (string) $count,
                        $slotLength,
                        '0',
                        STR_PAD_LEFT
                    );
                },
                // Must be kept last to avoid interfering with other callbacks
                '/P+/' => function ($matches) use ($prefix) {
                    $slot = $matches[0] ?? '';
                    $slotLength = strlen($slot);
                    $valueLength = strlen($prefix);

                    if ($slotLength < 1) {
                        return '';
                    }

                    if (! $prefix) {
                        throw new \Exception("The serial Number format includes a {$slotLength} long Prefix (S), but no prefix has been specified.");
                    }

                    if ($valueLength > $slotLength) {
                        throw new \Exception("The Serial Number can't be formatted: Prefix ({$prefix}) is {$valueLength} digits long while the format has only $slotLength slots.");
                    }

                    return str_pad(
                        (string) $prefix,
                        $slotLength,
                        '0',
                        STR_PAD_LEFT
                    );
                },
            ],
            $this->format
        );
    }

    /**
     * @return array{ 'prefix': ?string, 'serie': ?int, 'month': ?int, 'year': ?int, 'count': ?int}
     */
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
