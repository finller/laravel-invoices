<?php

namespace Finller\Invoice;

use Illuminate\Contracts\Database\Eloquent\Castable;

class MoneyCaster implements Castable
{

    /**
     * Get the name of the caster class to use when casting from / to this cast target.
     *
     * @param  array  $arguments
     * @return string
     */
    public static function castUsing(array $arguments)
    {
        return config('invoices.money_cast');
    }
}
