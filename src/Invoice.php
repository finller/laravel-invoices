<?php

namespace Finller\Invoice;

use Brick\Money\Money;
use Carbon\Carbon;
use Elegantly\Money\MoneyCast;
use Exception;
use Finller\Invoice\Casts\Discounts;
use Finller\Invoice\Database\Factories\InvoiceFactory;
use Illuminate\Contracts\Mail\Attachable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\ArrayObject;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Mail\Attachment;

/**
 * @property int $id
 * @property ?int $parent_id
 * @property ?Invoice $parent
 * @property ?Invoice $quote
 * @property ?Invoice $credit
 * @property InvoiceType $type
 * @property string $description
 * @property ?ArrayObject<string, mixed> $seller_information
 * @property ?ArrayObject<string, mixed> $buyer_information
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
 * @property ?ArrayObject<array-key, mixed> $metadata
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
    /**
     * @use HasFactory<InvoiceFactory>
     */
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
            $invoice->denormalize();
        });

        static::deleting(function (Invoice $invoice) {
            if (config('invoices.cascade_invoice_delete_to_invoice_items')) {
                $invoice->items()->delete();
            }
        });
    }

    /**
     * @return HasMany<InvoiceItem, $this>
     */
    public function items(): HasMany
    {
        /** @var class-string<InvoiceItem> */
        $model = config()->string('invoices.model_invoice_item');

        return $this->hasMany($model);
    }

    /**
     * Any model that is the "parent" of the invoice like a Mission, a Transaction, ...
     *
     * @return MorphTo<Model, $this>
     **/
    public function invoiceable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Typically, the buyer is one of your users, teams or any other model.
     * When editing your invoice, you should not rely on the information of this relation as they can change in time and impact all buyer's invoices.
     * Instead you should store the buyer information in his property on the invoice creation/validation.
     *
     * @return MorphTo<Model, $this>
     */
    public function buyer(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * In case, your application is a marketplace, you would also attach the invoice to the seller
     * When editing your invoice, you should not rely on the information of this relation as they can change in time and impact all seller's invoices.
     * Instead you should store the seller information in his property on the invoice creation/validation.
     *
     * @return MorphTo<Model, $this>
     */
    public function seller(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Invoice can be attached with another one
     * A Quote or a Credit can have another Invoice as parent.
     * Ex: $invoice = $quote->parent and $quote = $invoice->quote
     *
     * @return BelongsTo<Invoice, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * @return HasOne<Invoice, $this>
     */
    public function quote(): HasOne
    {
        return $this->hasOne(Invoice::class, 'parent_id')->where('type', InvoiceType::Quote);
    }

    /**
     * @return HasOne<Invoice, $this>
     */
    public function credit(): HasOne
    {
        return $this->hasOne(Invoice::class, 'parent_id')->where('type', InvoiceType::Credit);
    }

    /**
     * Generates a new serial number for an invoice.
     *
     * The count value for the new serial number is based on the previous serial number.
     * This function can be customized to determine what constitutes the previous invoice.
     */
    public function getPreviousInvoice(): ?static
    {
        /** @var ?static $invoice */
        $invoice = static::query()
            ->withoutGlobalScopes()
            ->where('serial_number_prefix', $this->serial_number_prefix)
            ->where('serial_number_serie', $this->serial_number_serie)
            ->where('serial_number_year', $this->serial_number_year)
            ->where('serial_number_month', $this->serial_number_month)
            ->latest('serial_number_count')
            ->first();

        return $invoice;
    }

    public function setSerialNumberPrefix(
        ?string $value = null,
        bool $throw = true,
    ): static {

        if ($value === null) {
            $this->serial_number_prefix = null;
        } elseif ($length = substr_count($this->serial_number_format, 'P')) {
            $this->serial_number_prefix = substr($value, -$length);
        } elseif ($throw) {
            throw new Exception('The Serial Number Format does not contain a prefix.');
        }

        return $this;
    }

    public function setSerialNumberSerie(
        null|int|string $value = null,
        bool $throw = true,
    ): static {

        if ($value === null) {
            $this->serial_number_serie = null;
        } elseif ($length = substr_count($this->serial_number_format, 'S')) {
            $this->serial_number_serie = (int) substr((string) $value, -$length);
        } elseif ($throw) {
            throw new Exception('The Serial Number Format does not contain a serie.');
        }

        return $this;
    }

    public function setSerialNumberYear(
        null|int|string $value = null,
        bool $throw = true,
    ): static {

        if ($value === null) {
            $this->serial_number_year = null;
        } elseif ($length = substr_count($this->serial_number_format, 'Y')) {
            $this->serial_number_year = (int) substr((string) $value, -$length);
        } elseif ($throw) {
            throw new Exception('The Serial Number Format does not contain a year.');
        }

        return $this;
    }

    public function setSerialNumberMonth(
        null|int|string $value = null,
        bool $throw = true,
    ): static {

        if ($value === null) {
            $this->serial_number_month = null;
        } elseif ($length = substr_count($this->serial_number_format, 'M')) {
            $this->serial_number_month = (int) substr((string) $value, -$length);
        } elseif ($throw) {
            throw new Exception('The Serial Number Format does not contain a month.');
        }

        return $this;
    }

    public function configureSerialNumber(
        ?string $format = null,
        ?string $prefix = null,
        string|int|null $serie = null,
        string|int|null $year = null,
        string|int|null $month = null,
        bool $throw = false,
    ): static {
        $this->serial_number_format = $format ?? $this->serial_number_format ?? InvoiceServiceProvider::getSerialNumberFormatConfiguration($this->type);

        return $this
            ->setSerialNumberPrefix($prefix, $throw)
            ->setSerialNumberSerie($serie, $throw)
            ->setSerialNumberYear($year, $throw)
            ->setSerialNumberMonth($month, $throw);
    }

    public function generateSerialNumber(): static
    {
        $this->configureSerialNumber(
            format: $this->serial_number_format,
            prefix: $this->serial_number_prefix ?? InvoiceServiceProvider::getSerialNumberPrefixConfiguration($this->type),
            serie: $this->serial_number_serie,
            year: $this->serial_number_year ?? now()->format('Y'),
            month: $this->serial_number_month ?? now()->format('m'),
        );

        $generator = new SerialNumberGenerator($this->serial_number_format);

        $previousCount = (int) $this->getPreviousInvoice()?->serial_number_count;

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

    /**
     * @return array{ 'prefix': ?string, 'serie': ?int, 'month': ?int, 'year': ?int, 'count': ?int}
     */
    public function parseSerialNumber(): array
    {
        $format = $this->serial_number_format ?? InvoiceServiceProvider::getSerialNumberFormatConfiguration($this->type);

        $generator = new SerialNumberGenerator($format);

        return $generator->parse($this->serial_number);
    }

    public function denormalizeSerialNumber(): static
    {
        $this->serial_number_format ??= InvoiceServiceProvider::getSerialNumberFormatConfiguration($this->type);

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

    /**
     * @param  Builder<Invoice>  $query
     * @return Builder<Invoice>
     */
    public function scopeInvoice(Builder $query): Builder
    {
        return $query->where('type', InvoiceType::Invoice);
    }

    /**
     * @param  Builder<Invoice>  $query
     * @return Builder<Invoice>
     */
    public function scopeCredit(Builder $query): Builder
    {
        return $query->where('type', InvoiceType::Credit);
    }

    /**
     * @param  Builder<Invoice>  $query
     * @return Builder<Invoice>
     */
    public function scopeQuote(Builder $query): Builder
    {
        return $query->where('type', InvoiceType::Quote);
    }

    /**
     * @param  Builder<Invoice>  $query
     * @return Builder<Invoice>
     */
    public function scopePaid(Builder $query): Builder
    {
        return $query->where('state', InvoiceState::Paid);
    }

    /**
     * @param  Builder<Invoice>  $query
     * @return Builder<Invoice>
     */
    public function scopeRefunded(Builder $query): Builder
    {
        return $query->where('state', InvoiceState::Refunded);
    }

    /**
     * @param  Builder<Invoice>  $query
     * @return Builder<Invoice>
     */
    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('state', InvoiceState::Draft);
    }

    /**
     * @param  Builder<Invoice>  $query
     * @return Builder<Invoice>
     */
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
            name: $this->type->getLabel(),
            state: $this->state->getLabel(),
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
