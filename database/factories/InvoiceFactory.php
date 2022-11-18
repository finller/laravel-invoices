<?php

namespace Finller\Invoice\Database\Factories;

use Finller\Invoice\Invoice;
use Finller\Invoice\SerialNumberGenerator;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition()
    {
        $date = fake()->dateTime();

        return [
            'serial_number' => (new SerialNumberGenerator())->generate(count: fake()->numberBetween(0, 100)),
            'state' => fake()->randomElement(['paid', 'pending']),
            'state_set_at' => $date,
            'due_at' => fake()->dateTimeBetween($date),
            'description' => fake()->sentence(),
        ];
    }
}
