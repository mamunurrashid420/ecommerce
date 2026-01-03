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
        // For PostgreSQL, we need to alter the enum type
        // Drop the old enum type first (this will fail if column uses it, so we'll handle that)
        DB::statement("DROP TYPE IF EXISTS order_status_enum CASCADE");
        
        // Create new enum type with all statuses
        DB::statement("CREATE TYPE order_status_enum AS ENUM (
            'cancelled',
            'pending_payment',
            'pending_payment_verification',
            'partially_paid',
            'purchasing',
            'purchase_completed',
            'shipped_from_supplier',
            'received_in_china_warehouse',
            'on_the_way_to_china_airport',
            'received_in_china_airport',
            'on_the_way_to_bd_airport',
            'received_in_bd_airport',
            'on_the_way_to_bd_warehouse',
            'received_in_bd_warehouse',
            'processing_for_delivery',
            'on_the_way_to_delivery',
            'completed',
            'processing_for_refund',
            'refunded',
            'pending',
            'processing',
            'shipped',
            'delivered'
        )");
        
        // Add a temporary column with the new enum type
        DB::statement("ALTER TABLE orders ADD COLUMN status_new order_status_enum DEFAULT 'pending'");
        
        // Copy data from old column to new column
        DB::statement("UPDATE orders SET status_new = status::text::order_status_enum WHERE status::text IN (
            'pending', 'processing', 'shipped', 'delivered', 'cancelled'
        )");
        
        // Drop the old column
        DB::statement("ALTER TABLE orders DROP COLUMN status");
        
        // Rename the new column to status
        DB::statement("ALTER TABLE orders RENAME COLUMN status_new TO status");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to old enum
        DB::statement("ALTER TABLE orders ALTER COLUMN status TYPE text");
        DB::statement("DROP TYPE IF EXISTS order_status_enum");
        DB::statement("CREATE TYPE order_status_enum AS ENUM ('pending', 'processing', 'shipped', 'delivered', 'cancelled')");
        DB::statement("ALTER TABLE orders ALTER COLUMN status TYPE order_status_enum USING status::order_status_enum");
    }
};
