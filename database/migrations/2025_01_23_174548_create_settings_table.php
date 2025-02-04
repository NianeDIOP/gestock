<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nom de la quincaillerie
            $table->string('address')->nullable(); // Adresse
            $table->string('phone')->nullable(); // Numéro de téléphone
            $table->string('ninea')->nullable(); // NINEA
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('settings');
    }
};
