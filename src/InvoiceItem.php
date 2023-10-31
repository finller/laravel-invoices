<?php

namespace Finller\Invoice;

use Brick\Money\Money;
use Carbon\Carbon;
use Finller\Money\MoneyCast;
use Illuminate\Database\Eloquent\Casts\ArrayObject;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property ?Money $unit_price
 * @property ?Money $unit_tax
 * @property ?float $tax_percentage between 0 and 100
 * @property ?string $currency
 * @property ?int $quantity
 * @property ?string $quantity_unit
 * @property ?string $label
 * @property ?string $description
 * @property ?ArrayObject $metadata
 * @property int $invoice_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class InvoiceItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        /**
         * This cast will be forwarded to the class defined in config at invoices.money_cast
         */
        'unit_price' => MoneyCast::class.':currency',
        'unit_tax' => MoneyCast::class.':currency',
        'metadata' => AsArrayObject::class,
        'tax_percentage' => 'float',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(config('invoices.model_invoice'));
    }

    public function toPdfInvoiceItem(): PdfInvoiceItem
    {
        return new PdfInvoiceItem(
            label: $this->label,
            quantity: $this->quantity,
            quantity_unit: $this->quantity_unit,
            description: $this->description,
            unit_price: $this->unit_price,
            unit_tax: $this->unit_tax,
            tax_percentage: $this->tax_percentage,
            currency: $this->currency,
        );
    }
}
