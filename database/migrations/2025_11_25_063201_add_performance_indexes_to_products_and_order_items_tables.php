<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();
        
        // Use IF NOT EXISTS for PostgreSQL, or try-catch for MySQL
        if ($driver === 'pgsql') {
            // PostgreSQL: Use CREATE INDEX IF NOT EXISTS
            DB::statement('CREATE INDEX IF NOT EXISTS idx_products_active_created ON products (is_active, created_at)');
            DB::statement('CREATE INDEX IF NOT EXISTS idx_products_category_active ON products (category_id, is_active)');
            DB::statement('CREATE INDEX IF NOT EXISTS idx_order_items_product_quantity ON order_items (product_id, quantity)');
            
            if (Schema::hasTable('product_media')) {
                DB::statement('CREATE INDEX IF NOT EXISTS idx_product_media_product_sort ON product_media (product_id, sort_order)');
                DB::statement('CREATE INDEX IF NOT EXISTS idx_product_media_thumbnail ON product_media (product_id, is_thumbnail)');
            }
        } else {
            // MySQL/MariaDB: Use Schema builder with try-catch
            try {
                Schema::table('products', function (Blueprint $table) {
                    if (!$this->hasIndex('products', 'idx_products_active_created')) {
                        $table->index(['is_active', 'created_at'], 'idx_products_active_created');
                    }
                    if (!$this->hasIndex('products', 'idx_products_category_active')) {
                        $table->index(['category_id', 'is_active'], 'idx_products_category_active');
                    }
                });
            } catch (\Exception $e) {
                // Index might already exist, continue
            }

            try {
                Schema::table('order_items', function (Blueprint $table) {
                    if (!$this->hasIndex('order_items', 'idx_order_items_product_quantity')) {
                        $table->index(['product_id', 'quantity'], 'idx_order_items_product_quantity');
                    }
                });
            } catch (\Exception $e) {
                // Index might already exist, continue
            }

            if (Schema::hasTable('product_media')) {
                try {
                    Schema::table('product_media', function (Blueprint $table) {
                        if (!$this->hasIndex('product_media', 'idx_product_media_product_sort')) {
                            $table->index(['product_id', 'sort_order'], 'idx_product_media_product_sort');
                        }
                        if (!$this->hasIndex('product_media', 'idx_product_media_thumbnail')) {
                            $table->index(['product_id', 'is_thumbnail'], 'idx_product_media_thumbnail');
                        }
                    });
                } catch (\Exception $e) {
                    // Index might already exist, continue
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::connection()->getDriverName();
        
        if ($driver === 'pgsql') {
            // PostgreSQL: Use DROP INDEX IF EXISTS
            DB::statement('DROP INDEX IF EXISTS idx_products_active_created');
            DB::statement('DROP INDEX IF EXISTS idx_products_category_active');
            DB::statement('DROP INDEX IF EXISTS idx_order_items_product_quantity');
            
            if (Schema::hasTable('product_media')) {
                DB::statement('DROP INDEX IF EXISTS idx_product_media_product_sort');
                DB::statement('DROP INDEX IF EXISTS idx_product_media_thumbnail');
            }
        } else {
            // MySQL/MariaDB: Use Schema builder with try-catch
            try {
                Schema::table('products', function (Blueprint $table) {
                    $table->dropIndex('idx_products_active_created');
                });
            } catch (\Exception $e) {
                // Index might not exist, continue
            }
            
            try {
                Schema::table('products', function (Blueprint $table) {
                    $table->dropIndex('idx_products_category_active');
                });
            } catch (\Exception $e) {
                // Index might not exist, continue
            }

            try {
                Schema::table('order_items', function (Blueprint $table) {
                    $table->dropIndex('idx_order_items_product_quantity');
                });
            } catch (\Exception $e) {
                // Index might not exist, continue
            }

            if (Schema::hasTable('product_media')) {
                try {
                    Schema::table('product_media', function (Blueprint $table) {
                        $table->dropIndex('idx_product_media_product_sort');
                    });
                } catch (\Exception $e) {
                    // Index might not exist, continue
                }
                
                try {
                    Schema::table('product_media', function (Blueprint $table) {
                        $table->dropIndex('idx_product_media_thumbnail');
                    });
                } catch (\Exception $e) {
                    // Index might not exist, continue
                }
            }
        }
    }

    /**
     * Check if an index exists on a table (PostgreSQL compatible)
     */
    private function hasIndex(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();
        
        try {
            if ($driver === 'pgsql') {
                // PostgreSQL query
                $result = $connection->select(
                    "SELECT COUNT(*) as count 
                     FROM pg_indexes 
                     WHERE schemaname = 'public' 
                     AND tablename = ? 
                     AND indexname = ?",
                    [$table, $index]
                );
            } else {
                // MySQL/MariaDB query
                $databaseName = $connection->getDatabaseName();
                $result = $connection->select(
                    "SELECT COUNT(*) as count FROM information_schema.statistics 
                     WHERE table_schema = ? AND table_name = ? AND index_name = ?",
                    [$databaseName, $table, $index]
                );
            }
            
            return isset($result[0]) && $result[0]->count > 0;
        } catch (\Exception $e) {
            // If query fails, assume index doesn't exist
            return false;
        }
    }
};
