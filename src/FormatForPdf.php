<?php

namespace Finller\Invoice;

use Brick\Money\Money;
use NumberFormatter;

trait FormatForPdf
{
    public function formatMoney(?Money $money = null, ?string $locale = null): ?string
    {
        return $money ? str_replace("\xe2\x80\xaf", ' ', $money->formatTo($locale ?? app()->getLocale())) : null;
    }

    public function formatPercentage(null|float|int $percentage, ?string $locale = null): string|false|null
    {
        if (! $percentage) {
            return null;
        }

        $formatter = new NumberFormatter($locale ?? app()->getLocale(), NumberFormatter::PERCENT);

        return $formatter->format(($percentage > 1) ? ($percentage / 100) : $percentage);
    }
}
