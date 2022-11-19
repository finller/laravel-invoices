<?php

namespace Finller\Invoice;

use Brick\Money\Currency;
use Brick\Money\Money;

class PdfInvoiceItem
{
    public function __construct(
        public string $label,
        public Money $unit_price,
        public Money $unit_tax,
        public ?int $quantity = 1,
        public null|string|Currency $currency = null,
        public ?string $description = null,
        public ?string $quantity_unit = null,
    ) {
        if (!($currency instanceof Currency)) {
            $this->currency = Currency::of($currency ?? config('invoices.default_currency'));
        }
    }

    public function formatMoney(Money $money, ?string $locale = null)
    {
        return $money->formatTo($locale ?? app()->getLocale());
    }
}
