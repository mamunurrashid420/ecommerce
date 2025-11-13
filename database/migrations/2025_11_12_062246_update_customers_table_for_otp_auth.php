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
            // Make email nullable since we're using phone for authentication
            $table->string('email')->nullable()->change();
            
            // Make name nullable (can be updated in profile)
            $table->string('name')->nullable()->change();
            
            // Make password nullable since we're using OTP authentication
            $table->string('password')->nullable()->change();
            
            // Make phone unique and required
            $table->string('phone')->nullable(false)->unique()->change();
            
            // Add OTP fields
            $table->string('otp')->nullable()->after('password');
            $table->timestamp('otp_expires_at')->nullable()->after('otp');
            
            // Add profile picture field
            $table->string('profile_picture')->nullable()->after('address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['otp', 'otp_expires_at', 'profile_picture']);
            $table->string('email')->nullable(false)->change();
            $table->string('name')->nullable(false)->change();
            $table->string('password')->nullable(false)->change();
            $table->string('phone')->nullable()->change();
            $table->dropUnique(['phone']);
        });
    }
};
