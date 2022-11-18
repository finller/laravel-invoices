<?php

namespace Finller\Invoice;

class PdfInvoiceItem
{

    public function __construct(
        public string $label,
        public int $unit_price,
        public int $unit_tax,
        public int $quantity = 1,
        public ?string $currency = null,
        public ?string $description = null,
        public ?string $quantity_unit = null,
    ) {
        $this->currency = $currency ?? config('invoices.default_currency');
    }
}
