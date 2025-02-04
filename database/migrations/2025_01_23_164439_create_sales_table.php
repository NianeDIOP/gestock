<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('sale_number')->unique(); // Numéro unique de vente
            $table->dateTime('sale_date'); // Date de la vente
            $table->string('client_name'); // Nom du client
            $table->string('client_phone')->nullable(); // Téléphone du client
            $table->enum('payment_method', ['cash', 'card', 'other']); // Méthode de paiement
            $table->text('notes')->nullable(); // Notes sur la vente
            $table->string('payment_status'); // Statut du paiement
            $table->decimal('subtotal', 10, 2); // Sous-total de la vente
            $table->decimal('tax', 10, 2); // Montant de la TVA
            $table->decimal('total', 10, 2); // Total de la vente
            $table->json('products')->nullable(); // Produits associés, sous forme JSON
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sales');
    }
};
