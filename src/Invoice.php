<?php

namespace Finller\Invoice;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\ArrayObject;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $serial_number
 * @property ?ArrayObject $seller
 * @property ?ArrayObject $buyer
 * @property ?string $state
 * @property ?Carbon $state_set_at
 * @property ?Carbon $due_at
 * @property Collection<int, InvoiceItem> $items
 */
class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [];

    protected $casts = [
        'seller' => AsArrayObject::class,
        'buyer' => AsArrayObject::class,
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
    }

    public function items()
    {
        return $this->hasMany(config('invoices.model_invoice_item'));
    }

    /**
     * Custom your strategie to get the last created serial number
     * Query the last record in the database or use a cache for example
     */
    public static function getLatestSerialNumber(): ?string
    {
        /** @var ?static */
        $latestInvoice = static::lastest('serial_number')->first();

        return $latestInvoice?->serial_number;
    }

    public static function generateSerialNumber()
    {
        $generator = new SerialNumberGenerator();

        $latestSerialNumber = static::getLatestSerialNumber();

        $latestCount = $latestSerialNumber ? data_get($generator->parse($latestSerialNumber), 'COUNT', 0) : 0;

        return $generator->generate(
            serie: 0,
            date: now(),
            count: $latestCount + 1
        );
    }
}
