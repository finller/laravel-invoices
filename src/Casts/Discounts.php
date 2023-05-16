<?php

namespace Finller\Invoice\Casts;

use Finller\Invoice\InvoiceDiscount;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Database\Eloquent\Model;

class Discounts implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        $data = Json::decode($attributes[$key]);

        return is_array($data) ? array_map(fn (?array $item) => InvoiceDiscount::fromArray($item), $data) : null;

        return $value;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return [$key => Json::encode($value)];
    }
}
