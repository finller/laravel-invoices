<?php

declare(strict_types=1);

namespace Elegantly\Invoices;

use Elegantly\Invoices\Contracts\GenerateSerialNumber;

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
        $serie = (string) $serie;
        $month = (string) $month;
        $year = (string) $year;
        $prefix = (string) $prefix;
        $count = (string) $count;

        $value = preg_replace_callback_array(
            [
                '/S+/' => function ($matches) use ($serie) {
                    $slot = (string) ($matches[0] ?? '');
                    $slotLength = mb_strlen($slot);
                    $valueLength = mb_strlen($serie);

                    if ($slotLength < 1) {
                        return '';
                    }

                    if (! $serie) {
                        throw new \Exception("The serial Number format includes a {$slotLength} long Serie (S), but no serie has been specified.");
                    }

                    if ($valueLength > $slotLength) {
                        throw new \Exception("The Serial Number can't be formatted: Serie ({$serie}) is {$valueLength} digits long while the format has only {$slotLength} slots.");
                    }

                    return mb_str_pad(
                        $serie,
                        $slotLength,
                        '0',
                        STR_PAD_LEFT
                    );
                },
                '/M+/' => function ($matches) use ($month) {
                    $slot = (string) ($matches[0] ?? '');
                    $slotLength = mb_strlen($slot);

                    if ($slotLength < 1) {
                        return '';
                    }

                    if (! $month) {
                        throw new \Exception("The serial Number format includes a {$slotLength} long Month (M), but no month has been specified.");
                    }

                    return mb_str_pad(
                        mb_substr($month, -$slotLength),
                        $slotLength,
                        '0',
                        STR_PAD_LEFT
                    );
                },
                '/Y+/' => function ($matches) use ($year) {
                    $slot = (string) ($matches[0] ?? '');
                    $slotLength = mb_strlen($slot);

                    if ($slotLength < 1) {
                        return '';
                    }

                    if (! $year) {
                        throw new \Exception("The serial Number format includes a {$slotLength} long Year (Y), but no year has been specified.");
                    }

                    return mb_str_pad(
                        mb_substr($year, -$slotLength),
                        $slotLength,
                        '0',
                        STR_PAD_LEFT
                    );
                },
                '/C+/' => function ($matches) use ($count) {
                    $slot = (string) ($matches[0] ?? '');
                    $slotLength = mb_strlen($slot);
                    $valueLength = mb_strlen($count);

                    if ($slotLength < 1) {
                        return '';
                    }

                    if (! $count) {
                        throw new \Exception("The serial Number format includes a {$slotLength} long Count (C), but no count has been specified.");
                    }

                    if ($valueLength > $slotLength) {
                        throw new \Exception("The Serial Number can't be formatted: Count ({$count}) is {$valueLength} digits long while the format has only {$slotLength} slots.");
                    }

                    return mb_str_pad(
                        $count,
                        $slotLength,
                        '0',
                        STR_PAD_LEFT
                    );
                },
                // Must be kept last to avoid interfering with other callbacks
                '/P+/' => function ($matches) use ($prefix) {
                    $slot = (string) ($matches[0] ?? '');
                    $slotLength = mb_strlen($slot);
                    $valueLength = mb_strlen($prefix);

                    if ($slotLength < 1) {
                        return '';
                    }

                    if (! $prefix) {
                        throw new \Exception("The serial Number format includes a {$slotLength} long Prefix (S), but no prefix has been specified.");
                    }

                    if ($valueLength > $slotLength) {
                        throw new \Exception("The Serial Number can't be formatted: Prefix ({$prefix}) is {$valueLength} digits long while the format has only {$slotLength} slots.");
                    }

                    return mb_str_pad(
                        $prefix,
                        $slotLength,
                        '0',
                        STR_PAD_LEFT
                    );
                },
            ],
            $this->format
        );

        return is_string($value) ? $value : '';
    }

    /**
     * @return array{ 'prefix': ?string, 'serie': ?int, 'month': ?int, 'year': ?int, 'count': ?int}
     */
    public function parse(string $serialNumber): array
    {
        preg_match("/{$this->formatToRegex()}/", $serialNumber, $matches);

        return [
            'prefix' => ($prefix = $matches['prefix'] ?? null) ? $prefix : null,
            'serie' => ($serie = $matches['serie'] ?? null) ? (int) $serie : null,
            'month' => ($month = $matches['month'] ?? null) ? (int) $month : null,
            'year' => ($year = $matches['year'] ?? null) ? (int) $year : null,
            'count' => ($count = $matches['count'] ?? null) ? (int) $count : null,
        ];
    }

    protected function formatToRegex(): string
    {
        $value = preg_replace_callback_array(
            [
                '/[^\w\s]/i' => fn ($matches) => "\\{$matches[0]}",
                '/P+/' => fn ($matches) => ($length = mb_strlen($matches[0])) ? "(?<prefix>[a-zA-Z]{{$length}})" : '',
                '/S+/' => fn ($matches) => ($length = mb_strlen($matches[0])) ? "(?<serie>\d{{$length}})" : '',
                '/M+/' => fn ($matches) => ($length = mb_strlen($matches[0])) ? "(?<month>\d{{$length}})" : '',
                '/Y+/' => fn ($matches) => ($length = mb_strlen($matches[0])) ? "(?<year>\d{{$length}})" : '',
                '/C+/' => fn ($matches) => ($length = mb_strlen($matches[0])) ? "(?<count>\d{{$length}})" : '',
            ],
            $this->format
        );

        return is_string($value) ? $value : '';
    }
}
