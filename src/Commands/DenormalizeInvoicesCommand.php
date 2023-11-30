<?php

namespace Finller\Invoice\Commands;

use Finller\Invoice\Invoice;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class DenormalizeInvoicesCommand extends Command
{
    public $signature = 'invoices:denormalize {ids?*}';

    public $description = 'Denormalize amount, tax and discounts to the invoice table';

    public function handle(): int
    {
        $ids = $this->argument('ids');

        $model = config('invoices.model_invoice');

        /** @var Builder $query */
        $query = $model::query();
        $query
            ->with(['items'])
            ->when($ids, fn (Builder $q) => $q->whereIn('id', $ids));

        /** @var int */
        $total = $query->count();

        $bar = $this->output->createProgressBar($total);

        $query
            ->chunk(200, function (Collection $invoices) use ($bar) {
                $invoices->each(function (Invoice $invoice) use ($bar) {
                    $invoice->denormalize()->saveQuietly();
                    $bar->advance();
                });
            });

        $bar->finish();

        return self::SUCCESS;
    }
}
