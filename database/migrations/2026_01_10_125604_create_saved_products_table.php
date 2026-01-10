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
        Schema::create('saved_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->string('product_id'); // Can be dropship product ID (e.g., "542673445219")
            $table->string('product_code')->nullable(); // Product code from dropship
            $table->string('product_name');
            $table->string('product_price');
            $table->string('product_image')->nullable();
            $table->string('product_sku')->nullable();
            $table->string('product_slug')->nullable(); // For linking to product detail page
            $table->timestamps();

            // Indexes for faster lookups
            $table->index('customer_id');
            $table->unique(['customer_id', 'product_id']); // Prevent duplicate saves
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saved_products');
    }
};
