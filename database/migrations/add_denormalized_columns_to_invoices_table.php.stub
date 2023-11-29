<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->bigInteger('subtotal_amount')->nullable();
            $table->bigInteger('discount_amount')->nullable();
            $table->bigInteger('tax_amount')->nullable();
            $table->bigInteger('total_amount')->nullable();
            $table->string('currency')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('subtotal_amount');
            $table->dropColumn('discount_amount');
            $table->dropColumn('tax_amount');
            $table->dropColumn('total_amount');
            $table->dropColumn('currency');
        });
    }
};
