<?php

namespace Finller\Invoice\Commands;

use Illuminate\Console\Command;

class InvoiceCommand extends Command
{
    public $signature = 'laravel-invoices';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
