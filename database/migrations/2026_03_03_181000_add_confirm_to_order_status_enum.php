<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For PostgreSQL, we need to add the 'confirm' value to the existing enum
        DB::statement("ALTER TYPE order_status_enum ADD VALUE IF NOT EXISTS 'confirm' AFTER 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Note: PostgreSQL doesn't support removing enum values directly
        // You would need to recreate the enum type to remove a value
        // For now, we'll leave it as is since removing enum values is complex
    }
};

