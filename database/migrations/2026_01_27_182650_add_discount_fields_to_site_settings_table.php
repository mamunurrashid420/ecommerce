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
            $table->integer('min_item_number_discount')->nullable()->after('min_product_quantity');
            $table->decimal('discount_percentage_on_item', 5, 2)->nullable()->after('min_item_number_discount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['min_item_number_discount', 'discount_percentage_on_item']);
        });
    }
};
