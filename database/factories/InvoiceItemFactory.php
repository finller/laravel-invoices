<?php

namespace Finller\Invoice\Database\Factories;

use Brick\Money\Money;
use Finller\Invoice\InvoiceItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceItemFactory extends Factory
{
    protected $model = InvoiceItem::class;

    public function definition()
    {
        $price = Money::ofMinor(fake()->numberBetween(1000, 100000), config('invoices.default_currency'));
        $unit_tax = Money::ofMinor(fake()->numberBetween(0, $price->getAmount()->toFloat()), config('invoices.default_currency'));

        $useTaxPercentage = fake()->boolean();

        return [
            'label' => fake()->sentence(),
            'description' => fake()->sentence(),
            'unit_price' => $price->getMinorAmount()->toInt(),
            'currency' => $price->getCurrency()->getCurrencyCode(),
            'unit_tax' => ! $useTaxPercentage ? $unit_tax : null,
            'tax_percentage' => $useTaxPercentage ? fake()->numberBetween(0, 100) : null,
            'quantity' => fake()->numberBetween(1, 10),
        ];
    }
}
