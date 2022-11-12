<?php

namespace Finller\Invoice;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [];

    protected $casts = [
        /**
         * This cast will be forwarded to the class defined in config at invoices.money_cast
         */
        'unit_price' => MoneyCaster::class,
        'unit_tax'=> MoneyCaster::class,
    ];


    public function invoice()
    {
        return $this->belongsTo(config('invoices.model_invoice'));
    }
}
