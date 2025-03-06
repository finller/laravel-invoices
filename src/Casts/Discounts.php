<?php

declare(strict_types=1);

namespace Elegantly\Invoices\Casts;

use Elegantly\Invoices\InvoiceDiscount;
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
     * @return InvoiceDiscount[]
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        /**
         * @var null|array<array-key, null|array{
         *      name: ?string,
         *      code: ?string,
         *      currency: ?string,
         *      amount_off: ?int,
         *      percent_off: ?float,
         * }> $data
         */
        $data = Json::decode($attributes[$key] ?? '');

        /** @var class-string<InvoiceDiscount> $class */
        $class = config()->string('invoices.discount_class');

        if (! is_array($data)) {
            return [];
        }

        return array_map(fn ($item) => $class::fromArray($item), $data);
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
        return [
            $key => blank($value) ? null : Json::encode($value),
        ];
    }
}
