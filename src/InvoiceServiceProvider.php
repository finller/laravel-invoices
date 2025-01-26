<?php

declare(strict_types=1);

namespace Finller\Invoice;

use Exception;
use Finller\Invoice\Commands\DenormalizeInvoicesCommand;
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
            ->hasViews()
            ->hasTranslations()
            ->hasCommand(DenormalizeInvoicesCommand::class)
            ->hasMigration('create_invoices_table')
            ->hasMigration('create_invoice_items_table')
            ->hasMigration('add_discounts_column_to_invoices_table')
            ->hasMigration('add_type_column_to_invoices_table')
            ->hasMigration('add_denormalized_columns_to_invoices_table')
            ->hasMigration('add_serial_number_details_columns_to_invoices_table')
            ->hasMigration('migrate_serial_number_details_columns_to_invoices_table');
    }

    public static function getSerialNumberPrefixConfiguration(?InvoiceType $type): ?string
    {
        /** @var string|array<string, string> $prefixes */
        $prefixes = config('invoices.serial_number.prefix', '');

        if (is_string($prefixes)) {
            return $prefixes;
        }

        return $prefixes[$type?->value] ?? null;
    }

    public static function getSerialNumberFormatConfiguration(?InvoiceType $type): string
    {
        /** @var string|array<string, string> $formats */
        $formats = config('invoices.serial_number.format') ?? '';

        if (is_string($formats)) {
            return $formats;
        }

        /** @var ?string $format */
        $format = $formats[$type?->value] ?? null;

        if (! $format) {
            throw new Exception("No serial number format defined in config for type: {$type?->value}.");
        }

        return $format;
    }
}
