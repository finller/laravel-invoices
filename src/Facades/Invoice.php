<?php

namespace Finller\Invoice\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Finller\Invoice\Invoice
 */
class Invoice extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Finller\Invoice\Invoice::class;
    }
}
