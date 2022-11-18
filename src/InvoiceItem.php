<?php

namespace Finller\Invoice;

use Brick\Money\Money;
use Carbon\Carbon;
use Finller\Money\MoneyCast;
use Illuminate\Database\Eloquent\Casts\ArrayObject;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property ?Money $unit_price
 * @property ?Money $unit_tax
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

    protected $fillable = [];

    protected $casts = [
        /**
         * This cast will be forwarded to the class defined in config at invoices.money_cast
         */
        'unit_price' => MoneyCast::class.'currency',
        'unit_tax' => MoneyCast::class.'currency',
        'metadata' => AsArrayObject::class,
    ];

    public function invoice()
    {
        return $this->belongsTo(config('invoices.model_invoice'));
    }

    public function toPdfInvoiceItem()
    {
        return new PdfInvoiceItem(
            label: $this->label,
            quantity: $this->quantity,
            quantity_unit: $this->quantity_unit,
            description: $this->description,
            unit_price: $this->unit_price?->getMinorAmount()->toInt(),
            unit_tax: $this->unit_tax?->getMinorAmount()->toInt(),
            currency: $this->currency,
        );
    }
}
