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
        // Add indexes to products table for landing page queries
        Schema::table('products', function (Blueprint $table) {
            // Index for filtering active products and ordering by created_at
            if (!$this->hasIndex('products', 'idx_products_active_created')) {
                $table->index(['is_active', 'created_at'], 'idx_products_active_created');
            }
            
            // Index for category_id and is_active (for category-based queries)
            if (!$this->hasIndex('products', 'idx_products_category_active')) {
                $table->index(['category_id', 'is_active'], 'idx_products_category_active');
            }
        });

        // Add indexes to order_items table for top selling products query
        Schema::table('order_items', function (Blueprint $table) {
            // Composite index for product_id and quantity (for SUM queries)
            if (!$this->hasIndex('order_items', 'idx_order_items_product_quantity')) {
                $table->index(['product_id', 'quantity'], 'idx_order_items_product_quantity');
            }
        });

        // Add indexes to product_media table for media queries
        if (Schema::hasTable('product_media')) {
            Schema::table('product_media', function (Blueprint $table) {
                // Index for product_id and sort_order (for ordered media queries)
                if (!$this->hasIndex('product_media', 'idx_product_media_product_sort')) {
                    $table->index(['product_id', 'sort_order'], 'idx_product_media_product_sort');
                }
                
                // Index for is_thumbnail lookups
                if (!$this->hasIndex('product_media', 'idx_product_media_thumbnail')) {
                    $table->index(['product_id', 'is_thumbnail'], 'idx_product_media_thumbnail');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if ($this->hasIndex('products', 'idx_products_active_created')) {
                $table->dropIndex('idx_products_active_created');
            }
            if ($this->hasIndex('products', 'idx_products_category_active')) {
                $table->dropIndex('idx_products_category_active');
            }
        });

        Schema::table('order_items', function (Blueprint $table) {
            if ($this->hasIndex('order_items', 'idx_order_items_product_quantity')) {
                $table->dropIndex('idx_order_items_product_quantity');
            }
        });

        if (Schema::hasTable('product_media')) {
            Schema::table('product_media', function (Blueprint $table) {
                if ($this->hasIndex('product_media', 'idx_product_media_product_sort')) {
                    $table->dropIndex('idx_product_media_product_sort');
                }
                if ($this->hasIndex('product_media', 'idx_product_media_thumbnail')) {
                    $table->dropIndex('idx_product_media_thumbnail');
                }
            });
        }
    }

    /**
     * Check if an index exists on a table
     */
    private function hasIndex(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $databaseName = $connection->getDatabaseName();
        
        try {
            $result = $connection->select(
                "SELECT COUNT(*) as count FROM information_schema.statistics 
                 WHERE table_schema = ? AND table_name = ? AND index_name = ?",
                [$databaseName, $table, $index]
            );
            
            return isset($result[0]) && $result[0]->count > 0;
        } catch (\Exception $e) {
            // If query fails, assume index doesn't exist
            return false;
        }
    }
};
