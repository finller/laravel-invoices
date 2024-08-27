<?php

namespace Finller\Invoice\Casts;

use Finller\Invoice\InvoiceDiscount;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Database\Eloquent\Model;

/**
 * @implements CastsAttributes<null|InvoiceDiscount[], array<string, mixed>>
 */
class Discounts implements CastsAttributes
{
    /**
     * @param  array<string, mixed>  $attributes
     * @return null|InvoiceDiscount[]
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        $data = Json::decode(data_get($attributes, $key, ''));

        /** @var string $class */
        $class = config('invoices.discount_class');

        return is_array($data) ? array_map(fn (?array $item) => $class::fromArray($item), $data) : null;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  null|InvoiceDiscount[]  $value
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return [$key => Json::encode($value)];
    }
}
