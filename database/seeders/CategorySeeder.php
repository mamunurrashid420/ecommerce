<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Parent Categories
        $parentCategories = [
            [
                'name' => 'Electronics',
                'description' => 'Electronic devices, gadgets, and technology products',
                'slug' => 'electronics',
                'icon' => 'fas fa-laptop',
                'sort_order' => 1,
                'is_active' => true,
                'is_featured' => true,
                'meta_title' => 'Electronics - Latest Tech & Gadgets',
                'meta_description' => 'Shop the latest electronics including smartphones, laptops, and tech accessories with great deals and fast shipping.',
                'meta_keywords' => 'electronics, technology, gadgets, smartphones, laptops, tech accessories'
            ],
            [
                'name' => 'Clothing',
                'description' => 'Fashion and apparel for men, women, and children',
                'slug' => 'clothing',
                'icon' => 'fas fa-tshirt',
                'sort_order' => 2,
                'is_active' => true,
                'is_featured' => true,
                'meta_title' => 'Clothing - Fashion & Apparel',
                'meta_description' => 'Discover the latest fashion trends with our wide selection of clothing for men, women, and children.',
                'meta_keywords' => 'clothing, fashion, apparel, mens wear, womens wear, kids clothing'
            ],
            [
                'name' => 'Home & Garden',
                'description' => 'Home improvement, furniture, and gardening supplies',
                'slug' => 'home-garden',
                'icon' => 'fas fa-home',
                'sort_order' => 3,
                'is_active' => true,
                'is_featured' => false,
                'meta_title' => 'Home & Garden - Furniture & Decor',
                'meta_description' => 'Transform your home and garden with our collection of furniture, decor, and gardening supplies.',
                'meta_keywords' => 'home, garden, furniture, decor, home improvement, gardening'
            ],
            [
                'name' => 'Sports & Outdoors',
                'description' => 'Sports equipment, outdoor gear, and fitness accessories',
                'slug' => 'sports-outdoors',
                'icon' => 'fas fa-running',
                'sort_order' => 4,
                'is_active' => true,
                'is_featured' => false,
                'meta_title' => 'Sports & Outdoors - Equipment & Gear',
                'meta_description' => 'Get active with our sports equipment, outdoor gear, and fitness accessories for all your adventures.',
                'meta_keywords' => 'sports, outdoors, fitness, equipment, gear, exercise'
            ],
            [
                'name' => 'Books & Media',
                'description' => 'Books, magazines, movies, and digital media',
                'slug' => 'books-media',
                'icon' => 'fas fa-book',
                'sort_order' => 5,
                'is_active' => true,
                'is_featured' => false,
                'meta_title' => 'Books & Media - Literature & Entertainment',
                'meta_description' => 'Explore our vast collection of books, magazines, movies, and digital media for entertainment and education.',
                'meta_keywords' => 'books, media, literature, movies, magazines, entertainment'
            ]
        ];

        foreach ($parentCategories as $categoryData) {
            $category = \App\Models\Category::create($categoryData);
            
            // Add child categories for some parent categories
            if ($category->slug === 'electronics') {
                $childCategories = [
                    [
                        'name' => 'Smartphones',
                        'description' => 'Latest smartphones and mobile accessories',
                        'slug' => 'smartphones',
                        'parent_id' => $category->id,
                        'icon' => 'fas fa-mobile-alt',
                        'sort_order' => 1,
                        'is_active' => true,
                        'is_featured' => false,
                        'meta_title' => 'Smartphones - Latest Mobile Phones',
                        'meta_description' => 'Shop the latest smartphones with cutting-edge features and competitive prices.',
                        'meta_keywords' => 'smartphones, mobile phones, android, iphone, mobile accessories'
                    ],
                    [
                        'name' => 'Laptops & Computers',
                        'description' => 'Laptops, desktops, and computer accessories',
                        'slug' => 'laptops-computers',
                        'parent_id' => $category->id,
                        'icon' => 'fas fa-laptop',
                        'sort_order' => 2,
                        'is_active' => true,
                        'is_featured' => false,
                        'meta_title' => 'Laptops & Computers - High Performance PCs',
                        'meta_description' => 'Find the perfect laptop or desktop computer for work, gaming, or everyday use.',
                        'meta_keywords' => 'laptops, computers, desktops, gaming pc, workstation'
                    ],
                    [
                        'name' => 'Audio & Headphones',
                        'description' => 'Headphones, speakers, and audio equipment',
                        'slug' => 'audio-headphones',
                        'parent_id' => $category->id,
                        'icon' => 'fas fa-headphones',
                        'sort_order' => 3,
                        'is_active' => true,
                        'is_featured' => false,
                        'meta_title' => 'Audio & Headphones - Premium Sound',
                        'meta_description' => 'Experience premium sound quality with our range of headphones, speakers, and audio equipment.',
                        'meta_keywords' => 'headphones, speakers, audio, sound, music, wireless'
                    ]
                ];
                
                foreach ($childCategories as $childData) {
                    \App\Models\Category::create($childData);
                }
            }
            
            if ($category->slug === 'clothing') {
                $childCategories = [
                    [
                        'name' => "Men's Clothing",
                        'description' => 'Fashion and apparel for men',
                        'slug' => 'mens-clothing',
                        'parent_id' => $category->id,
                        'icon' => 'fas fa-male',
                        'sort_order' => 1,
                        'is_active' => true,
                        'is_featured' => false,
                        'meta_title' => "Men's Clothing - Fashion for Men",
                        'meta_description' => 'Discover stylish and comfortable clothing options for men including shirts, pants, and accessories.',
                        'meta_keywords' => 'mens clothing, mens fashion, shirts, pants, mens wear'
                    ],
                    [
                        'name' => "Women's Clothing",
                        'description' => 'Fashion and apparel for women',
                        'slug' => 'womens-clothing',
                        'parent_id' => $category->id,
                        'icon' => 'fas fa-female',
                        'sort_order' => 2,
                        'is_active' => true,
                        'is_featured' => false,
                        'meta_title' => "Women's Clothing - Fashion for Women",
                        'meta_description' => 'Explore trendy and elegant clothing options for women including dresses, tops, and accessories.',
                        'meta_keywords' => 'womens clothing, womens fashion, dresses, tops, womens wear'
                    ]
                ];
                
                foreach ($childCategories as $childData) {
                    \App\Models\Category::create($childData);
                }
            }
        }
    }
}
