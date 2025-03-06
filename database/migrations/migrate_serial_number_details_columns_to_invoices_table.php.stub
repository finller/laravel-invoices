<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Collection;
use Elegantly\Invoices\Models\Invoice;
use Illuminate\Database\Eloquent\Model;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Invoice::query()
            ->chunkById(1_000, function (Collection $invoices) {
                /** @var Collection<int, Invoice> $invoices */

                foreach ($invoices as $invoice) {
                    Model::withoutTimestamps(
                        fn () => $invoice
                            ->denormalizeSerialNumber()
                            ->saveQuietly()
                    );
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       //
    }
};
