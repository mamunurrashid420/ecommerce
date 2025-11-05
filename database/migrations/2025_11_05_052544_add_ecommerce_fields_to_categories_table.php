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
        Schema::table('categories', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_id')->nullable()->after('id');
            $table->string('image_url')->nullable()->after('description');
            $table->string('icon', 100)->nullable()->after('image_url');
            $table->integer('sort_order')->default(0)->after('icon');
            $table->boolean('is_active')->default(true)->after('sort_order');
            $table->boolean('is_featured')->default(false)->after('is_active');
            $table->string('meta_title')->nullable()->after('is_featured');
            $table->text('meta_description')->nullable()->after('meta_title');
            $table->text('meta_keywords')->nullable()->after('meta_description');
            $table->unsignedBigInteger('created_by')->nullable()->after('meta_keywords');
            $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
            
            // Add foreign key constraints
            $table->foreign('parent_id')->references('id')->on('categories')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            
            // Add indexes for better performance
            $table->index(['is_active', 'sort_order']);
            $table->index(['is_featured', 'is_active']);
            $table->index(['parent_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            
            $table->dropIndex(['is_active', 'sort_order']);
            $table->dropIndex(['is_featured', 'is_active']);
            $table->dropIndex(['parent_id', 'is_active']);
            
            $table->dropColumn([
                'parent_id', 'image_url', 'icon', 'sort_order', 'is_active', 'is_featured',
                'meta_title', 'meta_description', 'meta_keywords', 'created_by', 'updated_by'
            ]);
        });
    }
};
