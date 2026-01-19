<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Offer;
use Carbon\Carbon;

class OfferSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $offers = [
            [
                'title' => 'Halloween Special Offer',
                'description' => 'Get your spooky smartwatch deals! Up to 50% off on selected items.',
                'url' => 'https://example.com/halloween-deals',
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 10,
                'available_start_time' => Carbon::now()->subDays(5),
                'available_end_time' => Carbon::now()->addDays(10),
            ],
            [
                'title' => 'Shipping & Logistics Solutions',
                'description' => 'Fast and reliable shipping services for your business needs.',
                'url' => 'https://example.com/shipping-solutions',
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 9,
                'available_start_time' => Carbon::now()->subDays(2),
                'available_end_time' => Carbon::now()->addDays(15),
            ],
            [
                'title' => 'Skincare Loved by Beauty Experts',
                'description' => 'Discover premium skincare products recommended by professionals.',
                'url' => 'https://example.com/skincare-collection',
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 8,
                'available_start_time' => Carbon::now()->subDays(1),
                'available_end_time' => null, // No end time - always available
            ],
            [
                'title' => 'Slay Your Makeup Game',
                'description' => 'Professional makeup products for the perfect look. Up to 40% off.',
                'url' => 'https://example.com/makeup-collection',
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 7,
                'available_start_time' => Carbon::now(),
                'available_end_time' => Carbon::now()->addDays(7),
            ],
            [
                'title' => 'Your Acne Breakup Solution',
                'description' => 'Effective acne treatment products. Say goodbye to breakouts!',
                'url' => 'https://example.com/acne-solutions',
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 6,
                'available_start_time' => Carbon::now()->subHours(12),
                'available_end_time' => Carbon::now()->addDays(20),
            ],
            [
                'title' => 'Achieve Glass Skin Goals',
                'description' => 'Get that perfect glass skin with our premium skincare routine.',
                'url' => 'https://example.com/glass-skin-routine',
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 5,
                'available_start_time' => Carbon::now()->addHours(2),
                'available_end_time' => Carbon::now()->addDays(30),
            ],
            [
                'title' => 'Flash Sale - Limited Time',
                'description' => 'Grab amazing deals before they expire! Limited quantities available.',
                'url' => 'https://example.com/flash-sale',
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 15,
                'available_start_time' => Carbon::now()->subHours(6),
                'available_end_time' => Carbon::now()->addHours(18), // Expires soon
            ],
            [
                'title' => 'Weekend Special Deals',
                'description' => 'Special weekend offers on electronics and gadgets.',
                'url' => 'https://example.com/weekend-deals',
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 4,
                'available_start_time' => Carbon::now()->startOfWeek()->addDays(5), // Friday
                'available_end_time' => Carbon::now()->startOfWeek()->addDays(6)->endOfDay(), // Sunday
            ],
            [
                'title' => 'New Arrivals Collection',
                'description' => 'Check out our latest products and trending items.',
                'url' => 'https://example.com/new-arrivals',
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 3,
                'available_start_time' => null, // Always available
                'available_end_time' => null,
            ],
            [
                'title' => 'Inactive Offer Example',
                'description' => 'This offer is currently inactive and should not appear in public listings.',
                'url' => 'https://example.com/inactive-offer',
                'is_featured' => false,
                'is_active' => false, // Inactive
                'sort_order' => 1,
                'available_start_time' => Carbon::now()->subDays(10),
                'available_end_time' => Carbon::now()->addDays(5),
            ],
        ];

        foreach ($offers as $offerData) {
            Offer::create($offerData);
        }

        $this->command->info('Created ' . count($offers) . ' sample offers');
    }
}