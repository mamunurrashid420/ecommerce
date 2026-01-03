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
        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->enum('category', ['A', 'B', 'C'])->index();
            $table->string('subcategory')->nullable()->index(); // For category C: mold_tape_garments, liquid_cosmetics, battery_powerbank, sunglasses
            $table->text('description_bn'); // Bangla description
            $table->text('description_en'); // English description
            $table->enum('shipping_method', ['air', 'ship'])->default('air')->index();
            $table->decimal('rate_per_kg', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            // Index for efficient queries
            $table->index(['category', 'subcategory', 'shipping_method', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_rates');
    }
};
