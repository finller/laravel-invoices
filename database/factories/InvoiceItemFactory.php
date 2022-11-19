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

        $useTaxPercentage = fake()->boolean();

        return [
            'label' => fake()->sentence(),
            'description' => fake()->sentence(),
            'unit_price' => $price,
            'unit_tax' => ! $useTaxPercentage ? fake()->numberBetween(0, $price) : null,
            'tax_percentage' => $useTaxPercentage ? fake()->numberBetween(0, 100) : null,
            'currency' => fake()->currencyCode(),
            'quantity' => fake()->numberBetween(1, 10),
        ];
    }
}
