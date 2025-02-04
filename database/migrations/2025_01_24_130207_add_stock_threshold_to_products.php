// database/migrations/xxxx_add_stock_threshold_to_products.php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('products', function (Blueprint $table) {
            $table->integer('stock_threshold')->default(6);
            $table->boolean('low_stock_alert')->default(false);
        });
    }

    public function down() {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['stock_threshold', 'low_stock_alert']);
        });
    }
};