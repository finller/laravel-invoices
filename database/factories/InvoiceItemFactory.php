<?php

namespace Finller\Invoice\Database\Factories;

use Finller\Invoice\InvoiceItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceItemFactory extends Factory
{
    protected $model = InvoiceItem::class;

    public function definition()
    {
        $price = fake()->numberBetween(100, 100000);

        return [
            'label' => fake()->sentence(),
            'description' => fake()->sentence(),
            'unit_price' => $price,
            'unit_tax' => fake()->numberBetween(0, $price),
            'currency' => 'USD',
        ];
    }
}
