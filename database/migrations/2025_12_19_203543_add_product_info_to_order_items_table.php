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
        Schema::table('order_items', function (Blueprint $table) {
            // Make product_id nullable for dropship products
            $table->foreignId('product_id')->nullable()->change();

            // Add product information fields for dropship products
            $table->string('product_code')->nullable()->after('product_id'); // item_id from 1688/TMAPI
            $table->string('product_name')->nullable()->after('product_code');
            $table->string('product_image')->nullable()->after('product_name');
            $table->string('product_sku')->nullable()->after('product_image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['product_code', 'product_name', 'product_image', 'product_sku']);

            // Revert product_id to NOT NULL (this might fail if there are null values)
            // $table->foreignId('product_id')->nullable(false)->change();
        });
    }
};
