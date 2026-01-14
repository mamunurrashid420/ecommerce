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
        Schema::create('upazillas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('district_id')->constrained('districts')->onDelete('cascade');
            $table->string('name'); // Upazilla name in English
            $table->string('name_bn')->nullable(); // Upazilla name in Bengali
            $table->integer('sort_order')->default(0); // For custom sorting
            $table->boolean('is_active')->default(true); // Active status
            $table->timestamps();

            // Indexes
            $table->index('district_id');
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
        Schema::dropIfExists('upazillas');
    }
};

