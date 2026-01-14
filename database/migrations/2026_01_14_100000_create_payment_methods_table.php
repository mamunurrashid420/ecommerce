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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Payment method name (e.g., Bkash, Nagad, Rocket)
            $table->string('name_bn')->nullable(); // Payment method name in Bengali
            $table->string('logo')->nullable(); // Logo image path
            $table->json('information')->nullable(); // Array of label_name and label_value pairs
            $table->text('description')->nullable(); // Optional description
            $table->text('description_bn')->nullable(); // Optional description in Bengali
            $table->integer('sort_order')->default(0); // For custom sorting
            $table->boolean('is_active')->default(true); // Active status
            $table->timestamps();

            // Indexes
            $table->index('name');
            $table->index('is_active');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};

