<?php

declare(strict_types=1);

namespace Elegantly\Invoices\Tests;

use Elegantly\Invoices\InvoiceServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Elegantly\\Invoices\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            InvoiceServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
        config()->set('money.default_currency', 'USD');

        $migration = include __DIR__.'/../database/migrations/create_invoices_table.php.stub';
        $migration->up();
        $migration = include __DIR__.'/../database/migrations/create_invoice_items_table.php.stub';
        $migration->up();
        $migration = include __DIR__.'/../database/migrations/add_type_column_to_invoices_table.php.stub';
        $migration->up();
        $migration = include __DIR__.'/../database/migrations/add_discounts_column_to_invoices_table.php.stub';
        $migration->up();
        $migration = include __DIR__.'/../database/migrations/add_denormalized_columns_to_invoices_table.php.stub';
        $migration->up();
        $migration = include __DIR__.'/../database/migrations/add_serial_number_details_columns_to_invoices_table.php.stub';
        $migration->up();
    }
}
