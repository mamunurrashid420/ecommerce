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
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('cascade');

            // Store minimal product information for cart display
            $table->string('product_code')->nullable(); // item_id from 1688/TMAPI
            $table->string('product_name');
            $table->decimal('product_price', 10, 2);
            $table->string('product_image')->nullable();
            $table->string('product_sku')->nullable();

            $table->integer('quantity')->default(1);
            $table->decimal('subtotal', 10, 2); // quantity * product_price

            $table->timestamps();

            // Indexes for faster lookups
            $table->index('cart_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
