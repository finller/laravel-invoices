<?php

namespace Finller\Invoice;

enum InvoiceState: string
{
    case Draft = 'draft';
    case Pending = 'pending';
    case Paid = 'paid';
    case Refunded = 'refunded';

    public function getLabel(): string
    {
        return match ($this) {
            self::Draft => __('invoices::invoice.states.draft'),
            self::Pending => __('invoices::invoice.states.pending'),
            self::Paid => __('invoices::invoice.states.paid'),
            self::Refunded => __('invoices::invoice.states.refunded'),
        };
    }

    public function trans(): string
    {
        return $this->getLabel();
    }
}
