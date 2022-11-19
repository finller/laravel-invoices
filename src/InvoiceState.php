<?php

namespace Finller\Invoice;

enum InvoiceState: string
{
    case Draft = 'draft';
    case Pending = 'pending';
    case Paid = 'paid';
    case Deleted = 'deleted';
}
