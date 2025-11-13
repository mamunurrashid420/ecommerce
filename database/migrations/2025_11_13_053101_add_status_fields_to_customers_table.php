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
        Schema::table('customers', function (Blueprint $table) {
            $table->boolean('is_banned')->default(false)->after('role');
            $table->boolean('is_suspended')->default(false)->after('is_banned');
            $table->timestamp('banned_at')->nullable()->after('is_suspended');
            $table->timestamp('suspended_at')->nullable()->after('banned_at');
            $table->text('ban_reason')->nullable()->after('banned_at');
            $table->text('suspend_reason')->nullable()->after('suspended_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'is_banned',
                'is_suspended',
                'banned_at',
                'suspended_at',
                'ban_reason',
                'suspend_reason'
            ]);
        });
    }
};
