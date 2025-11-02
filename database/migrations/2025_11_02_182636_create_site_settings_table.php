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
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            
            // Basic Site Information
            $table->string('title')->default('My Store');
            $table->string('tagline')->nullable();
            $table->text('description')->nullable();
            
            // Contact Information
            $table->string('contact_number')->nullable();
            $table->string('email')->nullable();
            $table->string('support_email')->nullable();
            $table->text('address')->nullable();
            
            // Business Information
            $table->string('business_name')->nullable();
            $table->string('business_registration_number')->nullable();
            $table->string('tax_number')->nullable();
            
            // Logos and Branding
            $table->string('header_logo')->nullable();
            $table->string('footer_logo')->nullable();
            $table->string('favicon')->nullable();
            
            // Social Media Links (JSON)
            $table->json('social_links')->nullable();
            
            // SEO Settings
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            
            // Ecommerce Settings
            $table->string('currency', 3)->default('USD');
            $table->string('currency_symbol', 10)->default('$');
            $table->enum('currency_position', ['before', 'after'])->default('before');
            $table->decimal('shipping_cost', 10, 2)->default(0);
            $table->decimal('free_shipping_threshold', 10, 2)->nullable();
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->boolean('tax_inclusive')->default(false);
            
            // Store Settings
            $table->boolean('store_enabled')->default(true);
            $table->string('store_mode')->default('live'); // live, maintenance, coming_soon
            $table->text('maintenance_message')->nullable();
            $table->json('business_hours')->nullable();
            
            // Payment & Shipping
            $table->json('payment_methods')->nullable();
            $table->json('shipping_methods')->nullable();
            $table->json('accepted_countries')->nullable();
            
            // Notifications
            $table->boolean('email_notifications')->default(true);
            $table->boolean('sms_notifications')->default(false);
            $table->string('notification_email')->nullable();
            
            // Analytics & Tracking
            $table->string('google_analytics_id')->nullable();
            $table->string('facebook_pixel_id')->nullable();
            $table->text('custom_scripts')->nullable();
            
            // Legal & Policies
            $table->text('terms_of_service')->nullable();
            $table->text('privacy_policy')->nullable();
            $table->text('return_policy')->nullable();
            $table->text('shipping_policy')->nullable();
            
            // Additional Settings (JSON for flexibility)
            $table->json('additional_settings')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
