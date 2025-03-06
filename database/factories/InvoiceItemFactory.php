<?php

declare(strict_types=1);

namespace Elegantly\Invoices\Database\Factories;

use Brick\Money\Money;
use Elegantly\Invoices\Models\InvoiceItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InvoiceItem>
 */
class InvoiceItemFactory extends Factory
{
    protected $model = InvoiceItem::class;

    public function definition()
    {
        $currency = config()->string('invoices.default_currency');

        $price = Money::ofMinor(fake()->numberBetween(1000, 100000), $currency);
        $unit_tax = Money::ofMinor(fake()->numberBetween(0, $price->getAmount()->toFloat()), $currency);

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
