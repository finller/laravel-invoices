<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('serial_number')->unique();
            
            $table->text('description')->nullable();

            /**
            * Store the type of tax collected and display the right label
            **/
            $table->string('tax_type')->nullable();
            $table->string('tax_exempt')->nullable(); // Tax exemption status like reverse charge

            /**
            * In most of the cases, you want to store the logo like it was when the invoice was created.
            * That's why we will store it in the database.
            **/
            $table->binary('logo')->nullable(); 

            $table->string('state')->index();
            $table->dateTime('state_set_at')->nullable();

            $table->dateTime('due_at')->nullable();

            /**
            * As invoices are a capture of a transaction at a specific moment in time,
            * You should store all the information in the model itself and limit dependencies to relations
            **/
            $table->json('buyer_information')->nullable();
            $table->json('seller_information')->nullable();

            /**
            * Attach the invoice to a transaction, a mission or any parent
            **/
            $table->nullableMorphs('invoiceable');

            /**
            * Typically this relationship will refer to your users, teams or companies
            **/
            $table->nullableMorphs('buyer');

            /**
            * If your application is a marketplace with both buyers and seller, you would certainly like to 
            * attach the invoice to both of them
            **/
            $table->nullableMorphs('seller');

            $table->json('metadata')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('invoices');
    }
};
