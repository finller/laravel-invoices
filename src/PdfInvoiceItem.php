<?php

namespace Finller\Invoice;

use Brick\Money\Currency;
use Brick\Money\Money;

class PdfInvoiceItem
{
    public function __construct(
        public string $label,
        public ?Money $unit_price,
        public ?Money $unit_tax,
        public ?int $quantity = 1,
        public null|string|Currency $currency = null,
        public ?string $description = null,
        public ?string $quantity_unit = null,
    ) {
        if (!($currency instanceof Currency)) {
            $this->currency = Currency::of($currency ?? config('invoices.default_currency'));
        }
    }

    public function formatMoney(?Money $money = null, ?string $locale = null)
    {
        return $money ? $money->formatTo($locale ?? app()->getLocale()) : null;
    }

    public function subTotalAmount(): Money
    {
        if ($this->unit_price === null) {
            return Money::ofMinor(0, $this->currency);
        }
        return $this->quantity ? $this->unit_price->multipliedBy($this->quantity) : $this->unit_price;
    }

    public function totalTaxAmount(): Money
    {
        if ($this->unit_tax === null) {
            return Money::ofMinor(0, $this->currency);
        }
        return $this->quantity ? $this->unit_tax->multipliedBy($this->quantity) : $this->unit_tax;
    }

    public function totalAmount(): Money
    {
        if ($this->unit_tax === null) {
            return $this->subTotalAmount();
        }

        return $this->subTotalAmount()->plus($this->totalTaxAmount());
    }
}
