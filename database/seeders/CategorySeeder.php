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
        $categories = [
            ['name' => 'Electronics', 'description' => 'Electronic devices and gadgets', 'slug' => 'electronics'],
            ['name' => 'Clothing', 'description' => 'Fashion and apparel', 'slug' => 'clothing'],
            ['name' => 'Books', 'description' => 'Books and literature', 'slug' => 'books'],
            ['name' => 'Home & Garden', 'description' => 'Home improvement and gardening', 'slug' => 'home-garden'],
            ['name' => 'Sports', 'description' => 'Sports equipment and accessories', 'slug' => 'sports'],
        ];

        foreach ($categories as $category) {
            \App\Models\Category::create($category);
        }
    }
}
