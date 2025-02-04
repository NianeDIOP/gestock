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
       Schema::create('quotations', function (Blueprint $table) {
           $table->id();
           $table->string('quotation_number')->unique();
           $table->dateTime('date');
           $table->string('client_name');
           $table->string('client_phone')->nullable();
           $table->string('client_email')->nullable();
           $table->text('notes')->nullable();
           $table->decimal('subtotal', 10, 2)->default(0);
           $table->decimal('tax', 10, 2)->default(0);
           $table->decimal('total', 10, 2)->default(0);
           $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
           $table->timestamps();
       });
    }
     
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
