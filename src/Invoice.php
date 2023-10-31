<?php

namespace Finller\Invoice;

use Carbon\Carbon;
use Finller\Invoice\Casts\Discounts;
use Illuminate\Contracts\Mail\Attachable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\ArrayObject;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Mail\Attachment;

/**
 * @property int $id
 * @property ?int $parent_id
 * @property InvoiceType $type
 * @property string $serial_number
 * @property ArrayObject $serial_number_details
 * @property string $description
 * @property ?ArrayObject $seller_information
 * @property ?ArrayObject $buyer_information
 * @property ?InvoiceState $state
 * @property ?Carbon $state_set_at
 * @property ?Carbon $due_at
 * @property ?string $tax_type
 * @property ?string $tax_exempt
 * @property Collection<int, InvoiceItem> $items
 * @property ?Model $buyer
 * @property ?int $buyer_id
 * @property ?string $buyer_type
 * @property ?Model $seller
 * @property ?int $seller_id
 * @property ?string $seller_type
 * @property ?Model $invoiceable
 * @property ?int $invoiceable_id
 * @property ?string $invoiceable_type
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property null|InvoiceDiscount[] $discounts
 * @property ?ArrayObject $metadata
 */
class Invoice extends Model implements Attachable
{
    use HasFactory;

    /**
     * Allow setting serie on the fly for the generation of the serialNumber
     */
    public ?int $serie = null;

    /**
     * Allow setting prefix on the fly for the generation of the serialNumber
     */
    public ?string $prefix = null;

    protected $fillable = [
        'serial_number',
        'description',
        'seller_information',
        'buyer_information',
        'state',
        'due_at',
        'state_set_at',
        'tax_type',
        'tax_exempt',
        'type',
    ];

    protected $casts = [
        'type' => InvoiceType::class,
        'state_set_at' => 'datetime',
        'due_at' => 'datetime',
        'state' => InvoiceState::class,
        'seller_information' => AsArrayObject::class,
        'buyer_information' => AsArrayObject::class,
        'metadata' => AsArrayObject::class,
        'discounts' => Discounts::class,
        'serial_number_details' => AsArrayObject::class,
    ];

    public static function booted()
    {
        static::creating(function (Invoice $invoice) {
            if (config('invoices.serial_number.auto_generate')) {
                $invoice->serial_number = $invoice->generateSerialNumber(
                    serie: $invoice->getSerialNumberSerie(),
                    date: now()
                );

                $invoice->serial_number_details = new ArrayObject(
                    $invoice->parseSerialNumber()
                );
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
     * Invoice can be atteched together
     * A Quote or a Credit can have another Invoice as parent
     */
    function parent(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    function quote(): HasOne
    {
        return $this->hasOne(Invoice::class)->where('type', InvoiceType::Quote->value);
    }

    function credit(): HasOne
    {
        return $this->hasOne(Invoice::class)->where('type', InvoiceType::Credit->value);
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

    public function getSerialNumberSerie(): ?int
    {
        return $this->serie;
    }

    public function getSerialNumberPrefix(): ?string
    {
        return $this->prefix ?? config('invoices.serial_number.prefix');
    }

    public function generateSerialNumber(int $serie = null, Carbon $date = null): string
    {
        $generator = new SerialNumberGenerator(prefix: $this->getSerialNumberPrefix());
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

    public function parseSerialNumber(): array
    {
        $generator = new SerialNumberGenerator();

        return $generator->parse(
            $this->serial_number
        );
    }

    public function getTaxLabel(): ?string
    {
        return null;
    }

    /**
     * @return null|InvoiceDiscount[]
     */
    public function getDiscounts(): ?array
    {
        return $this->discounts;
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('state', InvoiceState::Paid);
    }

    public function scopeRefunded(Builder $query): Builder
    {
        return $query->where('state', InvoiceState::Refunded);
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('state', InvoiceState::Draft);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('state', InvoiceState::Pending);
    }

    /**
     * Get the attachable representation of the model.
     */
    public function toMailAttachment(): Attachment
    {
        return Attachment::fromData(fn () => $this->toPdfInvoice()->pdf()->output())
            ->as($this->toPdfInvoice()->getFilename())
            ->withMime('application/pdf');
    }

    public function toPdfInvoice(): PdfInvoice
    {
        return new PdfInvoice(
            name: $this->type->trans(),
            serial_number: $this->serial_number,
            state: $this->state?->trans(),
            paid_at: ($this->state === InvoiceState::Paid) ? $this->state_set_at : null,
            due_at: $this->due_at,
            created_at: $this->created_at,
            buyer: $this->buyer_information?->toArray(),
            seller: $this->seller_information?->toArray(),
            description: $this->description,
            items: $this->items->map(fn (InvoiceItem $item) => $item->toPdfInvoiceItem())->all(),
            tax_label: $this->getTaxLabel(),
            discounts: $this->getDiscounts()
        );
    }
}
