<?php

namespace Finller\Invoice;

use Barryvdh\DomPDF\Facade\Pdf;
use Brick\Money\Money;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class PdfInvoice
{
    use FormatForPdf;

    /**
     * @param  null|PdfInvoiceItem[]  $items
     * @param  null|InvoiceDiscount[]  $discounts
     */
    public function __construct(
        public string $name,
        public string $serial_number,
        public string $state,
        public array $buyer,
        public Carbon $due_at,
        public Carbon $created_at,
        public ?Carbon $paid_at = null,
        public ?array $seller = null,
        public ?string $description = null,
        public ?string $logo = null,
        public ?string $template = null,
        public ?string $filename = null,
        public ?array $items = null,
        public ?string $tax_label = null,
        public ?array $discounts = null
    ) {
        $this->seller = $seller ?? config('invoices.default_seller', []);
        $this->logo = $logo ?? config('invoices.default_logo', null);
        $this->template = sprintf('invoices::%s', $template ?? config('invoices.default_template', null));
    }

    public function generateFilename(): string
    {
        return Str::slug("{$this->name}_{$this->serial_number}", separator: '_') . ".pdf";
    }

    public function getFilename(): string
    {
        return $this->filename ?? $this->generateFilename();
    }

    public function getLogo(): string
    {
        $type = pathinfo($this->logo, PATHINFO_EXTENSION);
        $data = file_get_contents($this->logo);

        return 'data:image/' . $type . ';base64,' . base64_encode($data);
    }

    /**
     * Before discount and taxes
     */
    public function subTotalAmount(): Money
    {
        if (empty($this->items)) {
            return Money::ofMinor(0, config('invoices.default_currency'));
        }

        $firstItem = Arr::first($this->items);

        $currency = $firstItem->currency;

        return array_reduce(
            $this->items,
            fn (Money $total, PdfInvoiceItem $item) => $total->plus($item->subTotalAmount()),
            Money::of(0, $currency)
        );
    }

    public function totalTaxAmount(): Money
    {
        if (empty($this->items)) {
            return Money::ofMinor(0, config('invoices.default_currency'));
        }

        $firstItem = Arr::first($this->items);

        $currency = $firstItem->currency;

        return array_reduce(
            $this->items,
            fn (Money $total, PdfInvoiceItem $item) => $total->plus($item->totalTaxAmount()),
            Money::of(0, $currency)
        );
    }

    public function totalDiscountAmount(): Money
    {
        if (!$this->discounts) {
            return Money::of(0, $this->subTotalAmount()->getCurrency());
        }

        $subtotal = $this->subTotalAmount();

        return array_reduce($this->discounts, function (Money $total, InvoiceDiscount $discount) use ($subtotal) {
            return $total->plus($discount->computeDiscountAmountOn($subtotal));
        }, Money::of(0, $subtotal->getCurrency()));
    }

    public function totalAmount(): Money
    {
        if (empty($this->items)) {
            return Money::ofMinor(0, config('invoices.default_currency'));
        }

        $firstItem = Arr::first($this->items);

        $currency = $firstItem->currency;

        $total = array_reduce(
            $this->items,
            fn (Money $total, PdfInvoiceItem $item) => $total->plus($item->totalAmount()),
            Money::of(0, $currency)
        );

        return $total->minus($this->totalDiscountAmount());
    }

    public function pdf(): \Barryvdh\DomPDF\PDF
    {
        $pdf = Pdf::setPaper(
            config('invoices.paper_options.paper', 'a4'),
            config('invoices.paper_options.orientation', 'portrait')
        );

        foreach (config('invoices.pdf_options') as $attribute => $value) {
            $pdf->setOption($attribute, $value);
        }

        return $pdf->loadView($this->template, ['invoice' => $this]);
    }

    public function stream(): Response
    {
        return $this->pdf()->stream($this->getFilename());
    }

    public function download(): Response
    {
        return $this->pdf()->download($this->getFilename());
    }

    public function view(): \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory
    {
        return view($this->template, ['invoice' => $this]);
    }
}
