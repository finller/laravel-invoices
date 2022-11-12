<?php

namespace Finller\Invoice;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class InvoiceServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-invoices')
            ->hasConfigFile()
            // ->hasViews()
            ->hasMigration('create_invoices_table')
            ->hasMigration('create_invoice_items_table');
        // ->hasCommand(InvoiceCommand::class);
    }
}
