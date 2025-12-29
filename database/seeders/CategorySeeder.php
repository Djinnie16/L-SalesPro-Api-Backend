<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            // Product Categories
            [
                'name' => 'Engine Oils',
                'slug' => 'engine-oils',
                'type' => 'product',
                'description' => 'Various types of engine oils',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Mineral Oils',
                'slug' => 'mineral-oils',
                'type' => 'product',
                'description' => 'Mineral based engine oils',
                'order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Synthetic Oils',
                'slug' => 'synthetic-oils',
                'type' => 'product',
                'description' => 'Synthetic engine oils',
                'order' => 3,
                'is_active' => true,
            ],
            // Customer Categories
            [
                'name' => 'A+',
                'slug' => 'a-plus',
                'type' => 'customer',
                'description' => 'Premium customers',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'A',
                'slug' => 'a',
                'type' => 'customer',
                'description' => 'Regular customers',
                'order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'B',
                'slug' => 'b',
                'type' => 'customer',
                'description' => 'Standard customers',
                'order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'C',
                'slug' => 'c',
                'type' => 'customer',
                'description' => 'New customers',
                'order' => 4,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}