<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Smartphone X1',
                'description' => 'Latest smartphone with advanced features',
                'long_description' => 'The Smartphone X1 represents the pinnacle of mobile technology, featuring a stunning 6.7-inch OLED display, advanced camera system with AI-powered photography, and lightning-fast 5G connectivity. Built with premium materials and designed for the modern user.',
                'price' => 699.99,
                'stock_quantity' => 50,
                'sku' => 'PHONE-X1-001',
                'category_id' => 1,
                'brand' => 'TechCorp',
                'model' => 'X1 Pro',
                'weight' => 0.18,
                'dimensions' => '160.8 x 78.1 x 7.4 mm',
                'tags' => json_encode(['smartphone', '5G', 'premium', 'camera']),
                'meta_title' => 'Smartphone X1 - Premium 5G Mobile Phone | TechCorp',
                'meta_description' => 'Discover the Smartphone X1 with advanced features, 5G connectivity, and premium design. Perfect for professionals and tech enthusiasts.',
                'meta_keywords' => 'smartphone, 5G, mobile phone, TechCorp, premium',
                'slug' => 'smartphone-x1-pro',
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => 'Laptop Pro 15',
                'description' => 'High-performance laptop for professionals',
                'long_description' => 'The Laptop Pro 15 is engineered for professionals who demand the best. Featuring the latest Intel processor, dedicated graphics, and a stunning 15.6-inch 4K display. Perfect for creative work, programming, and business applications.',
                'price' => 1299.99,
                'stock_quantity' => 25,
                'sku' => 'LAPTOP-PRO-15',
                'category_id' => 1,
                'brand' => 'ProTech',
                'model' => 'Pro 15 Elite',
                'weight' => 2.1,
                'dimensions' => '356 x 243 x 17.9 mm',
                'tags' => json_encode(['laptop', 'professional', '4K', 'Intel']),
                'meta_title' => 'Laptop Pro 15 - Professional 4K Laptop | ProTech',
                'meta_description' => 'High-performance Laptop Pro 15 with 4K display, Intel processor, and professional features for demanding users.',
                'meta_keywords' => 'laptop, professional, 4K, Intel, ProTech',
                'slug' => 'laptop-pro-15-elite',
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => 'Cotton T-Shirt',
                'description' => 'Comfortable cotton t-shirt in various colors',
                'long_description' => '100% organic cotton t-shirt that combines comfort with style. Available in multiple colors and sizes, this premium t-shirt is perfect for casual wear and everyday comfort.',
                'price' => 19.99,
                'stock_quantity' => 100,
                'sku' => 'TSHIRT-COT-001',
                'category_id' => 2,
                'brand' => 'ComfortWear',
                'model' => 'Classic Fit',
                'weight' => 0.15,
                'dimensions' => 'Various sizes available',
                'tags' => json_encode(['cotton', 'organic', 'casual', 'comfortable']),
                'meta_title' => 'Organic Cotton T-Shirt - Comfortable Casual Wear | ComfortWear',
                'meta_description' => '100% organic cotton t-shirt in various colors. Comfortable, stylish, and perfect for everyday wear.',
                'meta_keywords' => 'cotton t-shirt, organic, casual wear, comfortable',
                'slug' => 'organic-cotton-t-shirt',
                'created_by' => 1,
                'updated_by' => 1,
            ],
        ];

        foreach ($products as $productData) {
            $product = \App\Models\Product::create($productData);
            
            // Add sample media for each product
            $mediaData = [
                [
                    'product_id' => $product->id,
                    'type' => 'image',
                    'url' => 'https://via.placeholder.com/800x600?text=' . urlencode($product->name . ' - Main'),
                    'alt_text' => $product->name . ' main image',
                    'title' => $product->name,
                    'is_thumbnail' => true,
                    'sort_order' => 1,
                ],
                [
                    'product_id' => $product->id,
                    'type' => 'image',
                    'url' => 'https://via.placeholder.com/800x600?text=' . urlencode($product->name . ' - Side'),
                    'alt_text' => $product->name . ' side view',
                    'title' => $product->name . ' side view',
                    'is_thumbnail' => false,
                    'sort_order' => 2,
                ],
                [
                    'product_id' => $product->id,
                    'type' => 'video',
                    'url' => 'https://sample-videos.com/zip/10/mp4/SampleVideo_1280x720_1mb.mp4',
                    'alt_text' => $product->name . ' demo video',
                    'title' => $product->name . ' product demo',
                    'is_thumbnail' => false,
                    'sort_order' => 3,
                ],
            ];

            foreach ($mediaData as $media) {
                \App\Models\ProductMedia::create($media);
            }

            // Add sample custom fields
            $customFields = [];
            
            if ($product->category_id == 1) { // Electronics
                $customFields = [
                    ['label_name' => 'Processor', 'value' => 'Intel Core i7', 'field_type' => 'text'],
                    ['label_name' => 'RAM', 'value' => '16GB DDR4', 'field_type' => 'text'],
                    ['label_name' => 'Storage', 'value' => '512GB SSD', 'field_type' => 'text'],
                    ['label_name' => 'Display Size', 'value' => '15.6 inches', 'field_type' => 'text'],
                    ['label_name' => 'Battery Life', 'value' => '10 hours', 'field_type' => 'text'],
                    ['label_name' => 'Warranty', 'value' => '2 years', 'field_type' => 'text'],
                ];
            } elseif ($product->category_id == 2) { // Clothing
                $customFields = [
                    ['label_name' => 'Material', 'value' => '100% Organic Cotton', 'field_type' => 'text'],
                    ['label_name' => 'Fit', 'value' => 'Regular Fit', 'field_type' => 'text'],
                    ['label_name' => 'Care Instructions', 'value' => 'Machine wash cold', 'field_type' => 'text'],
                    ['label_name' => 'Available Sizes', 'value' => 'XS, S, M, L, XL, XXL', 'field_type' => 'text'],
                    ['label_name' => 'Available Colors', 'value' => 'Black, White, Navy, Gray', 'field_type' => 'text'],
                ];
            }

            foreach ($customFields as $index => $field) {
                \App\Models\ProductCustomField::create([
                    'product_id' => $product->id,
                    'label_name' => $field['label_name'],
                    'value' => $field['value'],
                    'field_type' => $field['field_type'],
                    'sort_order' => $index + 1,
                ]);
            }
        }
    }
}
