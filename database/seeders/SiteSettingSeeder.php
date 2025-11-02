<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SiteSetting::create([
            'title' => 'My Ecommerce Store',
            'tagline' => 'Your one-stop shop for everything',
            'description' => 'Welcome to our amazing ecommerce store where you can find the best products at great prices.',
            'contact_number' => '+1-234-567-8900',
            'email' => 'info@mystore.com',
            'support_email' => 'support@mystore.com',
            'address' => '123 Main Street, City, State 12345, Country',
            'business_name' => 'My Ecommerce Business LLC',
            'business_registration_number' => 'REG123456789',
            'tax_number' => 'TAX987654321',
            'social_links' => [
                'facebook' => 'https://facebook.com/mystore',
                'twitter' => 'https://twitter.com/mystore',
                'instagram' => 'https://instagram.com/mystore',
                'linkedin' => 'https://linkedin.com/company/mystore',
                'youtube' => 'https://youtube.com/mystore',
                'tiktok' => '',
                'whatsapp' => '+1234567890',
            ],
            'meta_title' => 'My Ecommerce Store - Best Products Online',
            'meta_description' => 'Shop the best products online at My Ecommerce Store. Fast shipping, great prices, and excellent customer service.',
            'meta_keywords' => 'ecommerce, online shopping, products, store, buy online',
            'currency' => 'USD',
            'currency_symbol' => '$',
            'currency_position' => 'before',
            'shipping_cost' => 9.99,
            'free_shipping_threshold' => 50.00,
            'tax_rate' => 8.25,
            'tax_inclusive' => false,
            'store_enabled' => true,
            'store_mode' => 'live',
            'business_hours' => [
                'monday' => ['open' => '09:00', 'close' => '17:00', 'closed' => false],
                'tuesday' => ['open' => '09:00', 'close' => '17:00', 'closed' => false],
                'wednesday' => ['open' => '09:00', 'close' => '17:00', 'closed' => false],
                'thursday' => ['open' => '09:00', 'close' => '17:00', 'closed' => false],
                'friday' => ['open' => '09:00', 'close' => '17:00', 'closed' => false],
                'saturday' => ['open' => '10:00', 'close' => '16:00', 'closed' => false],
                'sunday' => ['open' => '12:00', 'close' => '16:00', 'closed' => false],
            ],           
 'payment_methods' => [
                'credit_card' => ['enabled' => true, 'name' => 'Credit Card'],
                'paypal' => ['enabled' => true, 'name' => 'PayPal'],
                'stripe' => ['enabled' => true, 'name' => 'Stripe'],
                'bank_transfer' => ['enabled' => false, 'name' => 'Bank Transfer'],
                'cash_on_delivery' => ['enabled' => true, 'name' => 'Cash on Delivery'],
            ],
            'shipping_methods' => [
                'standard' => ['enabled' => true, 'name' => 'Standard Shipping', 'cost' => 9.99, 'days' => '5-7'],
                'express' => ['enabled' => true, 'name' => 'Express Shipping', 'cost' => 19.99, 'days' => '2-3'],
                'overnight' => ['enabled' => true, 'name' => 'Overnight Shipping', 'cost' => 39.99, 'days' => '1'],
                'pickup' => ['enabled' => true, 'name' => 'Store Pickup', 'cost' => 0, 'days' => '0'],
            ],
            'accepted_countries' => [
                'US' => 'United States',
                'CA' => 'Canada',
                'GB' => 'United Kingdom',
                'AU' => 'Australia',
                'DE' => 'Germany',
                'FR' => 'France',
            ],
            'email_notifications' => true,
            'sms_notifications' => false,
            'notification_email' => 'notifications@mystore.com',
            'terms_of_service' => 'By using our website, you agree to our terms of service...',
            'privacy_policy' => 'We respect your privacy and are committed to protecting your personal data...',
            'return_policy' => 'We offer a 30-day return policy for all items in original condition...',
            'shipping_policy' => 'We ship worldwide and offer various shipping options...',
            'additional_settings' => [
                'allow_guest_checkout' => true,
                'require_account_activation' => false,
                'min_order_amount' => 10.00,
                'max_order_amount' => 10000.00,
                'inventory_tracking' => true,
                'show_out_of_stock' => true,
                'allow_backorders' => false,
            ],
        ]);
    }
}