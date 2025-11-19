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
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('cancellation_requested_at')->nullable()->after('status');
            $table->text('cancellation_reason')->nullable()->after('cancellation_requested_at');
            $table->enum('cancellation_requested_by', ['customer', 'admin'])->nullable()->after('cancellation_reason');
            $table->timestamp('cancelled_at')->nullable()->after('cancellation_requested_by');
            $table->enum('cancelled_by', ['customer', 'admin'])->nullable()->after('cancelled_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'cancellation_requested_at',
                'cancellation_reason',
                'cancellation_requested_by',
                'cancelled_at',
                'cancelled_by'
            ]);
        });
    }
};
