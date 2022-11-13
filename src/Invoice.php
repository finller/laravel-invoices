<?php

namespace Finller\Invoice;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\ArrayObject;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property string $serial_number
 * @property string $description
 * @property ?ArrayObject $seller_information
 * @property ?ArrayObject $buyer_information
 * @property ?string $state
 * @property ?Carbon $state_set_at
 * @property ?Carbon $due_at
 * @property Collection<int, InvoiceItem> $items
 * @property ?Model $buyer
 * @property ?Model $seller
 * @property ?Model $invoiceable
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [];

    protected $casts = [
        'metadata' => AsArrayObject::class,
        'seller_information' => AsArrayObject::class,
        'buyer_information' => AsArrayObject::class,
        'state_set_at' => 'datetime',
        'due_at' => 'datetime',
    ];

    public static function booted()
    {
        static::creating(function (Invoice $invoice) {
            if (config('invoices.serial_number.auto_generate')) {
                $invoice->serial_number = static::generateSerialNumber();
            }
        });

        static::deleting(function (Invoice $invoice) {
            if (config('invoices.cascade_invoice_delete_to_invoice_items')) {
                $invoice->items()->delete();
            }
        });
    }

    public function items()
    {
        return $this->hasMany(config('invoices.model_invoice_item'));
    }

    /**
     * Custom your strategy to get the last created serial number
     * Query the last record in the database or use a cache for example
     */
    public static function getLatestSerialNumber(): ?string
    {
        /** @var ?static */
        $latestInvoice = static::query()->latest('serial_number')->first();

        return $latestInvoice?->serial_number;
    }

    public static function generateSerialNumber(?int $serie = null, ?Carbon $date = null): string
    {
        $latestSerialNumber = static::getLatestSerialNumber();
        $generator = new SerialNumberGenerator();

        if ($latestSerialNumber) {
            $parsedSerialNumber = $generator->parse($latestSerialNumber);
            $latestCount = data_get($parsedSerialNumber, 'count', 0);
        } else {
            $latestCount = 0;
        }

        return $generator->generate(
            serie: $serie,
            date: $date ?? now(),
            count: $latestCount + 1
        );
    }

    /**
     * Any model that is the "parent" of the invoice like a Mission, a Transaction, ...
     **/
    public function invoiceable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Typically, the buyer is one of your users, teams or any other model.
     * When editing your invoice, you should not rely on the information of this relation as they can change in time and impact all buyer's invoices.
     * Instead you should store the buyer information in his property on the invoice creation/validation.
     */
    public function buyer(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * In case, your application is a marketplace, you would also attach the invoice to the seller
     * When editing your invoice, you should not rely on the information of this relation as they can change in time and impact all seller's invoices.
     * Instead you should store the seller information in his property on the invoice creation/validation.
     */
    public function seller(): MorphTo
    {
        return $this->morphTo();
    }
}
