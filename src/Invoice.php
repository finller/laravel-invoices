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
 * @property ?InvoiceState $state
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

    protected $fillable = [
        "serial_number",
        "description",
        "seller_information",
        "buyer_information",
        "state",
        "due_at",
        "state_set_at"
    ];

    protected $casts = [
        'state_set_at' => 'datetime',
        'due_at' => 'datetime',
        'state' => InvoiceState::class,
        'seller_information' => AsArrayObject::class,
        'buyer_information' => AsArrayObject::class,
        'metadata' => AsArrayObject::class,
    ];

    public static function booted()
    {
        static::creating(function (Invoice $invoice) {
            if (config('invoices.serial_number.auto_generate')) {
                $invoice->serial_number = $invoice->generateSerialNumber();
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

    /**
     * Custom your strategy to get the lastest created serial number
     * For example, you can query the last record in the database or use a cache
     */
    public function getLatestSerialNumber(): ?string
    {
        /** @var ?static */
        $latestInvoice = static::query()->latest('serial_number')->first();

        return $latestInvoice?->serial_number;
    }

    public function generateSerialNumber(?int $serie = null, ?Carbon $date = null): string
    {
        $generator = new SerialNumberGenerator();
        $latestSerialNumber = $this->getLatestSerialNumber();

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

    public function toPdfInvoice()
    {
        return new PdfInvoice(
            name: __('invoices::invoice.invoice'),
            serial_number: $this->serial_number,
            state: $this->state ? __("invoices::invoice.states.{$this->state->value}") : null,
            paid_at: ($this->state === InvoiceState::Paid) ? $this->state_set_at : null,
            due_at: $this->due_at,
            created_at: $this->created_at,
            buyer: $this->buyer_information?->toArray(),
            seller: $this->seller_information?->toArray(),
            description: $this->description,
            items: $this->items->map(fn (InvoiceItem $item) => $item->toPdfInvoiceItem())->all()
        );
    }
}
