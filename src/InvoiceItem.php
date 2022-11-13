<?php

namespace Finller\Invoice;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\ArrayObject;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property mixed $unit_price
 * @property mixed $unit_tax
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
        'unit_price' => MoneyCaster::class,
        'unit_tax' => MoneyCaster::class,
        'metadata' => AsArrayObject::class,
    ];

    public function invoice()
    {
        return $this->belongsTo(config('invoices.model_invoice'));
    }
}
