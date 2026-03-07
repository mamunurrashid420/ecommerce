<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('china_to_china_bill', 10, 2)->default(0)->after('shipping_cost');
            $table->decimal('china_to_bangladesh_bill', 10, 2)->default(0)->after('china_to_china_bill');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['china_to_china_bill', 'china_to_bangladesh_bill']);
        });
    }
};
