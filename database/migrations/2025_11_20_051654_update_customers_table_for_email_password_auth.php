<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Make email required and unique (for email/password authentication)
        // First, update any null emails to a temporary unique value
        \DB::statement("UPDATE customers SET email = CONCAT('temp_', id, '@temp.com') WHERE email IS NULL");
        
        // Drop unique constraint if it exists (to avoid duplicate constraint error)
        \DB::statement("ALTER TABLE customers DROP CONSTRAINT IF EXISTS customers_email_unique");
        
        Schema::table('customers', function (Blueprint $table) {
            // Make email required and unique
            $table->string('email')->nullable(false)->unique()->change();
            
            // Make password required (for email/password authentication)
            // First, set a default password for customers without password
            \DB::statement('UPDATE customers SET password = ? WHERE password IS NULL', [
                Hash::make('default_password_change_me')
            ]);
            
            $table->string('password')->nullable(false)->change();
            
            // Make name required (for better user experience)
            \DB::statement("UPDATE customers SET name = COALESCE(name, 'Customer') WHERE name IS NULL");
            $table->string('name')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Revert to nullable (for OTP authentication)
            $table->string('email')->nullable()->change();
            $table->dropUnique(['email']);
            $table->string('password')->nullable()->change();
            $table->string('name')->nullable()->change();
        });
    }
};
