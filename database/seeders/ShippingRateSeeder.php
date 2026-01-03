<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ShippingRate;

class ShippingRateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Category A - Single record with both air and ship rates
        ShippingRate::create([
            'category' => 'A',
            'subcategory' => null,
            'description_bn' => ' জুতা, ব্যাগ, জুয়েলারী, যন্ত্রপাতি, স্টিকার, ইলেকট্রনিক্স, কম্পিউটার এক্সেসরীস, সিরামিক, ধাতব, চামরা, রাবার, প্লাস্টিক জাতীয় পন্য, ব্যাটারি ব্যাতিত খেলনা।',
            'description_en' => 'Shoes, bags, jewelry, tools, stickers, electronics, computer accessories, ceramics, metal, leather, rubber, plastic products, toys excluding batteries.',
            'rate_air' => 750.00,
            'rate_ship' => 500.00,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        // Category B - Single record with both air and ship rates
        ShippingRate::create([
            'category' => 'B',
            'subcategory' => null,
            'description_bn' => 'ব্যাটারি জাতীয় যেকোণ পন্য, ডুপ্লিকেট ব্রান্ড বা কপিঁ পন্য, জীবন্ত উদ্ভিদ, বীজ, রাসায়নীক দ্রব্য, নেটওয়ার্কিং আইটেম, Bluetooth হেডফোন, ম্যাগনেট বা লেজার জাতীয় পন্য।',
            'description_en' => 'Battery products, duplicate/copy brands, live plants, seeds, chemical products, networking items, Bluetooth headphones, magnet or laser products.',
            'rate_air' => 1150.00,
            'rate_ship' => 1000.00,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        // Category C - Mold/Tape/Garments - Single record with both rates
        ShippingRate::create([
            'category' => 'C',
            'subcategory' => null,
            'description_bn' => 'মোল্ড/টেপ, পোশাক বা যেকোন গার্মেন্টস আইটেম',
            'description_en' => 'Mold/tape, clothing or any garments items',
            'rate_air' => 780.00,
            'rate_ship' => 650.00,
            'is_active' => true,
            'sort_order' => 1,
        ]);

    }
}
