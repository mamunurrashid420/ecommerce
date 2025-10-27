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
        Schema::table('products', function (Blueprint $table) {
            // SEO Fields
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->string('slug')->unique()->nullable();
            
            // Audit Fields
            $table->foreignId('created_by')->nullable()->constrained('customers')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('customers')->onDelete('set null');
            
            // Additional Product Fields
            $table->text('long_description')->nullable();
            $table->decimal('weight', 8, 2)->nullable();
            $table->string('dimensions')->nullable();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->json('tags')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'meta_title', 'meta_description', 'meta_keywords', 'slug',
                'created_by', 'updated_by', 'long_description', 'weight',
                'dimensions', 'brand', 'model', 'tags'
            ]);
        });
    }
};
