<?php

namespace Finller\Invoice;

use Brick\Money\Currency;
use Brick\Money\Money;
use Exception;

class PdfInvoiceItem
{
    use FormatForPdf;

    public function __construct(
        public string $label,
        public ?Money $unit_price = null,
        public ?Money $unit_tax = null,
        public ?float $tax_percentage = null,
        public ?int $quantity = 1,
        public null|string|Currency $currency = null,
        public ?string $description = null,
        public ?string $quantity_unit = null,
    ) {
        if (! ($currency instanceof Currency)) {
            $this->currency = Currency::of($currency ?? config('invoices.default_currency'));
        }

        if ($tax_percentage && ($tax_percentage > 100 || $tax_percentage < 0)) {
            throw new Exception("The tax_percentage parameter must be an integer between 0 and 100. $tax_percentage given.");
        }
    }

    public function subTotalAmount(): Money
    {
        if ($this->unit_price === null) {
            return Money::ofMinor(0, $this->currency);
        }

        return $this->quantity !== null ? $this->unit_price->multipliedBy($this->quantity) : $this->unit_price;
    }

    public function totalTaxAmount(): Money
    {
        if ($this->unit_tax) {
            return $this->quantity !== null ? $this->unit_tax->multipliedBy($this->quantity) : $this->unit_tax;
        }

        if ($this->tax_percentage) {
            [$tax] = $this->subTotalAmount()->allocate($this->tax_percentage, 100 - $this->tax_percentage);

            return $tax;
        }

        return Money::ofMinor(0, $this->currency);
    }

    public function totalAmount(): Money
    {
        return $this->subTotalAmount()->plus($this->totalTaxAmount());
    }
}
