<?php

namespace Finller\Invoice;

use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class PdfInvoice
{
    /**
     * @param  null|PdfInvoiceItem[]  $items
     */
    public function __construct(
        public string $name,
        public string $serial_number,
        public string $state,
        public Carbon $due_at,
        public Carbon $created_at,
        public array $buyer,
        public ?array $seller = null,
        public ?string $description = null,
        public ?string $logo = null,
        public ?string $template = null,
        public ?string $filename = null,
        public ?array $items = null
    ) {
        $this->seller = $seller ?? config('invoices.default_seller', []);
        $this->logo = $logo ?? config('invoices.default_logo', null);
        $this->template = sprintf('invoices::%s', $template ?? config('invoices.default_template', null));
    }

    public function generateFilename(): string
    {
        return Str::snake("{$this->name}_{$this->serial_number}");
    }

    public function getFilename(): string
    {
        return $this->filename ?? $this->generateFilename();
    }

    public function getLogo()
    {
        $type = pathinfo($this->logo, PATHINFO_EXTENSION);
        $data = file_get_contents($this->logo);

        return 'data:image/' . $type . ';base64,' . base64_encode($data);
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
