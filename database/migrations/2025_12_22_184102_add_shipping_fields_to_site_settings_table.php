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
        Schema::table('site_settings', function (Blueprint $table) {
            $table->integer('min_product_quantity')->default(1)->after('price_margin');
            $table->decimal('min_order_amount', 10, 2)->default(0)->after('min_product_quantity');
            $table->decimal('shipping_cost_by_ship', 10, 2)->default(0)->after('min_order_amount');
            $table->string('shipping_duration_by_ship')->nullable()->after('shipping_cost_by_ship');
            $table->decimal('shipping_cost_by_air', 10, 2)->default(0)->after('shipping_duration_by_ship');
            $table->string('shipping_duration_by_air')->nullable()->after('shipping_cost_by_air');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'min_product_quantity',
                'min_order_amount',
                'shipping_cost_by_ship',
                'shipping_duration_by_ship',
                'shipping_cost_by_air',
                'shipping_duration_by_air',
            ]);
        });
    }
};
