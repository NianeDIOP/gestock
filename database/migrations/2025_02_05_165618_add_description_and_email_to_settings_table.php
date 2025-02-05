<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->text('description')->nullable()->after('name');
            $table->string('email', 255)->nullable()->after('description');
        });
    }
    
    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['description', 'email']);
        });
    }
};
