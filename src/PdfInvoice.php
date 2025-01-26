<?php

declare(strict_types=1);

namespace Finller\Invoice;

use Barryvdh\DomPDF\Facade\Pdf;
use Brick\Money\Money;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;

class PdfInvoice
{
    use FormatForPdf;

    public string $template;

    public string $font;

    public string $color;

    /**
     * @param  array<string, mixed>  $buyer
     * @param  array<string, mixed>  $seller
     * @param  PdfInvoiceItem[]  $items
     * @param  InvoiceDiscount[]  $discounts
     * @param  ?string  $logo  A local file path. The file must be accessible using file_get_contents.
     */
    public function __construct(
        public ?string $name = null,
        public ?string $state = null,
        public ?string $serial_number = null,
        public array $seller = [],
        public array $buyer = [],
        public ?string $description = null,
        public ?Carbon $created_at = null,
        public ?Carbon $due_at = null,
        public ?Carbon $paid_at = null,
        public ?string $tax_label = null,
        public array $items = [],
        public array $discounts = [],
        public ?string $logo = null,
        public ?string $filename = null,
        ?string $color = null,
        ?string $template = null,
        ?string $font = null,
    ) {
        $this->name = $name ?? __('invoices::invoice.invoice');
        $this->seller = $seller ?: config()->array('invoices.default_seller');
        // @phpstan-ignore-next-line
        $this->logo = $logo ?? config('invoices.pdf.logo') ?? config('invoices.default_logo');
        // @phpstan-ignore-next-line
        $this->color = $color ?? config('invoices.pdf.color') ?? config('invoices.default_color');
        // @phpstan-ignore-next-line
        $this->font = $font ?? config('invoices.pdf.options.defaultFont');
        // @phpstan-ignore-next-line
        $this->template = sprintf('invoices::%s', $template ?? config('invoices.pdf.template') ?? config('invoices.default_template'));
    }

    public function generateFilename(): string
    {
        return "{$this->serial_number}.pdf";
    }

    public function getFilename(): string
    {
        return $this->filename ?? $this->generateFilename();
    }

    public function getCurrency(): string
    {
        /** @var ?PdfInvoiceItem $firstItem */
        $firstItem = Arr::first($this->items);

        return $firstItem?->currency->getCurrencyCode() ?? config()->string('invoices.default_currency');
    }

    /**
     * @deprecated Using $this->logo
     */
    public function getLogo(): ?string
    {
        return $this->logo;
    }

    /**
     * Before discount and taxes
     */
    public function subTotalAmount(): Money
    {
        return array_reduce(
            $this->items,
            fn (Money $total, PdfInvoiceItem $item) => $total->plus($item->subTotalAmount()),
            Money::of(0, $this->getCurrency())
        );
    }

    public function totalTaxAmount(): Money
    {
        return array_reduce(
            $this->items,
            fn (Money $total, PdfInvoiceItem $item) => $total->plus($item->totalTaxAmount()),
            Money::of(0, $this->getCurrency())
        );
    }

    public function totalDiscountAmount(): Money
    {
        if (! $this->discounts) {
            return Money::of(0, $this->getCurrency());
        }

        $subtotal = $this->subTotalAmount();

        return array_reduce($this->discounts, function (Money $total, InvoiceDiscount $discount) use ($subtotal) {
            return $total->plus($discount->computeDiscountAmountOn($subtotal));
        }, Money::of(0, $subtotal->getCurrency()));
    }

    public function totalAmount(): Money
    {
        $total = array_reduce(
            $this->items,
            fn (Money $total, PdfInvoiceItem $item) => $total->plus($item->totalAmount()),
            Money::of(0, $this->getCurrency())
        );

        return $total->minus($this->totalDiscountAmount());
    }

    /**
     * @param  array<string, mixed>  $options
     */
    public function pdf(array $options = []): \Barryvdh\DomPDF\PDF
    {
        $pdf = Pdf::setPaper(
            // @phpstan-ignore-next-line
            config('invoices.pdf.paper.paper') ?? config('invoices.paper_options.paper') ?? 'a4',
            // @phpstan-ignore-next-line
            config('invoices.pdf.paper.orientation') ?? config('invoices.paper_options.orientation') ?? 'portrait'
        );

        $allOptions = array_merge(
            // @phpstan-ignore-next-line
            config('invoices.pdf.options') ?? config('invoices.pdf_options') ?? [],
            $options,
        );

        foreach ($allOptions as $attribute => $value) {
            $pdf->setOption($attribute, $value);
        }

        return $pdf->loadView($this->template, [
            'invoice' => $this,
        ]);
    }

    public function stream(): Response
    {
        return $this->pdf()->stream($this->getFilename());
    }

    public function download(): Response
    {
        return $this->pdf()->download($this->getFilename());
    }

    public function view(): \Illuminate\Contracts\View\View
    {
        // @phpstan-ignore-next-line
        return view($this->template, ['invoice' => $this]);
    }
}
