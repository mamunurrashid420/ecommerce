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
            $table->string('payment_method')->default('manual')->after('status'); // manual, online, cod, etc.
            $table->string('payment_status')->default('pending')->after('payment_method'); // pending, paid, failed
            $table->string('transaction_number')->nullable()->after('payment_status');
            $table->string('payment_receipt_image')->nullable()->after('transaction_number');
            $table->timestamp('paid_at')->nullable()->after('payment_receipt_image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'payment_method',
                'payment_status',
                'transaction_number',
                'payment_receipt_image',
                'paid_at'
            ]);
        });
    }
};
