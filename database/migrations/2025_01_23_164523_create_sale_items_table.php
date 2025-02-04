<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->onDelete('cascade'); // Lien avec `sales`
            $table->foreignId('product_id')->constrained(); // Lien avec `products`
            $table->integer('quantity'); // Quantité de produit
            $table->decimal('unit_price', 10, 2); // Prix unitaire
            $table->decimal('subtotal', 10, 2); // Sous-total pour cet article
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sale_items');
    }
};
