<?php

namespace Finller\Invoice;

use Brick\Money\Money;
use Carbon\Carbon;
use Exception;
use Finller\Invoice\Casts\Discounts;
use Finller\Money\MoneyCast;
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
use phpDocumentor\Reflection\Types\This;

/**
 * @property int $id
 * @property ?int $parent_id
 * @property ?Invoice $parent
 * @property ?Invoice $quote
 * @property ?Invoice $credit
 * @property InvoiceType $type
 * @property string $description
 * @property ?ArrayObject $seller_information
 * @property ?ArrayObject $buyer_information
 * @property InvoiceState $state
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
 * @property ?Money $subtotal_amount
 * @property ?Money $discount_amount
 * @property ?Money $tax_amount
 * @property ?Money $total_amount
 * @property ?string $currency
 * @property string $serial_number
 * @property string $serial_number_format
 * @property ?string $serial_number_prefix
 * @property ?int $serial_number_serie
 * @property ?int $serial_number_year
 * @property ?int $serial_number_month
 * @property int $serial_number_count
 */
class Invoice extends Model implements Attachable
{
    use HasFactory;

    protected $attributes = [
        'type' => InvoiceType::Invoice,
        'state' => InvoiceState::Draft,
    ];

    protected $guarded = [];

    protected $casts = [
        'type' => InvoiceType::class,
        'state_set_at' => 'datetime',
        'due_at' => 'datetime',
        'state' => InvoiceState::class,
        'seller_information' => AsArrayObject::class,
        'buyer_information' => AsArrayObject::class,
        'metadata' => AsArrayObject::class,
        'discounts' => Discounts::class,
        'subtotal_amount' => MoneyCast::class.':currency',
        'discount_amount' => MoneyCast::class.':currency',
        'tax_amount' => MoneyCast::class.':currency',
        'total_amount' => MoneyCast::class.':currency',
    ];

    public static function booted()
    {
        static::creating(function (Invoice $invoice) {
            if (
                config('invoices.serial_number.auto_generate') &&
                blank($invoice->serial_number)
            ) {
                $invoice->generateSerialNumber();
            } else {
                $invoice->denormalizeSerialNumber();
            }
        });

        static::updating(function (Invoice $invoice) {
            if ($invoice->isDirty([
                'serial_number',
                'serial_number_format',
                'serial_number_prefix',
                'serial_number_serie',
                'serial_number_year',
                'serial_number_month',
                'serial_number_count',
            ])) {
                throw new Exception("Serial number details can't be changed after creation", 500);
            }

            $invoice->denormalize();
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
     * Invoice can be attached with another one
     * A Quote or a Credit can have another Invoice as parent.
     * Ex: $invoice = $quote->parent and $quote = $invoice->quote
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function quote(): HasOne
    {
        return $this->hasOne(Invoice::class, 'parent_id')->where('type', InvoiceType::Quote->value);
    }

    public function credit(): HasOne
    {
        return $this->hasOne(Invoice::class, 'parent_id')->where('type', InvoiceType::Credit->value);
    }

    /**
     * When creating a new serial number
     * The count value is based on the previous serial number
     *
     * This function can be customized to determine what is the previous invoice
     */
    public function getPreviousInvoice(): ?static
    {
        /** @var ?static $invoice */
        $invoice = static::query()
            ->where('serial_number_prefix', $this->serial_number_prefix)
            ->where('serial_number_serie', $this->serial_number_serie)
            ->where('serial_number_year', $this->serial_number_year)
            ->where('serial_number_month', $this->serial_number_month)
            ->latest('serial_number_count')
            ->first();

        return $invoice;
    }

    public function getSerialNumberFormatConfiguration(): string
    {
        /** @var string|array $formats */
        $formats = config('invoices.serial_number.format', '');

        if (is_string($formats)) {
            return $formats;
        }

        /** @var ?string $format */
        $format = data_get($formats, $this->type->value);

        if (! $format) {
            throw new Exception("No serial number format defined in conifg for type: {$this->type->value}.");
        }

        return $format;
    }

    public function getSerialNumberPrefixConfiguration(): ?string
    {
        /** @var string|array $prefixes */
        $prefixes = config('invoices.serial_number.prefix', '');

        if (is_string($prefixes)) {
            return $prefixes;
        }

        return data_get($prefixes, $this->type->value);
    }

    /**
     * Usefull to set serial number values while respecting format
     */
    public function configureSerialNumber(
        ?string $format = null,
        ?string $prefix = null,
        ?int $serie = null,
        string|int|null $year = null,
        string|int|null $month = null,
    ): static {
        $this->serial_number_format = $format ?? $this->serial_number_format ?? $this->getSerialNumberFormatConfiguration();

        $prefixLength = substr_count($this->serial_number_format, 'P');

        if ($prefixLength) {
            if ($prefix) {
                $this->serial_number_prefix = substr($prefix, -$prefixLength);
            }
        } else {
            $this->serial_number_prefix = null;
        }

        $serieLength = substr_count($this->serial_number_format, 'S');

        if ($serieLength) {
            if ($serie) {
                $this->serial_number_serie = (int) substr((string) $serie, -$serieLength);
            }
        } else {
            $this->serial_number_serie = null;
        }

        $yearLength = substr_count($this->serial_number_format, 'Y');

        if ($yearLength) {
            if ($year) {
                $this->serial_number_year = (int) substr((string) $year, -$yearLength);
            }
        } else {
            $this->serial_number_year = null;
        }

        $monthLength = substr_count($this->serial_number_format, 'M');

        if ($monthLength) {
            if ($month) {
                $this->serial_number_month = (int) substr((string) $month, -$monthLength);
            }
        } else {
            $this->serial_number_month = null;
        }

        return $this;
    }

    public function generateSerialNumber(): static
    {
        $this->configureSerialNumber(
            format: $this->serial_number_format,
            prefix: $this->serial_number_prefix ?? $this->getSerialNumberPrefixConfiguration(),
            serie: $this->serial_number_serie,
            year: $this->serial_number_year ?? now()->format('Y'),
            month: $this->serial_number_month ?? now()->format('m'),
        );

        $generator = new SerialNumberGenerator($this->serial_number_format);

        $previousCount = $this->getPreviousInvoice()?->serial_number_count ?? 0;

        $this->serial_number = $generator->generate(
            prefix: $this->serial_number_prefix,
            serie: $this->serial_number_serie,
            year: $this->serial_number_year,
            month: $this->serial_number_month,
            count: $previousCount + 1
        );

        $this->denormalizeSerialNumber();

        return $this;
    }

    public function parseSerialNumber(): array
    {
        $format = $this->serial_number_format ?? $this->getSerialNumberFormatConfiguration();

        $generator = new SerialNumberGenerator($format);

        return $generator->parse($this->serial_number);
    }

    public function denormalizeSerialNumber(): static
    {
        $this->serial_number_format ??= $this->getSerialNumberFormatConfiguration();

        $values = $this->parseSerialNumber();

        $this->serial_number_prefix = $values['prefix'];
        $this->serial_number_serie = $values['serie'];
        $this->serial_number_year = $values['year'];
        $this->serial_number_month = $values['month'];
        $this->serial_number_count = $values['count'];

        return $this;
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

    /**
     * Denormalize amounts computed from items to the invoice table
     * Allowing easier query
     */
    public function denormalize(): static
    {
        $pdfInvoice = $this->toPdfInvoice();
        $this->currency = $pdfInvoice->getCurrency();
        $this->subtotal_amount = $pdfInvoice->subTotalAmount();
        $this->discount_amount = $pdfInvoice->totalDiscountAmount();
        $this->tax_amount = $pdfInvoice->totalTaxAmount();
        $this->total_amount = $pdfInvoice->totalAmount();

        return $this;
    }

    public function scopeInvoice(Builder $query): Builder
    {
        return $query->where('type', InvoiceType::Invoice);
    }

    public function scopeCredit(Builder $query): Builder
    {
        return $query->where('type', InvoiceType::Credit);
    }

    public function scopeQuote(Builder $query): Builder
    {
        return $query->where('type', InvoiceType::Quote);
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
            state: $this->state->trans(),
            serial_number: $this->serial_number,
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
