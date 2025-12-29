<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $engineOilCategory = Category::where('slug', 'engine-oils')->first();
        $mineralOilCategory = Category::where('slug', 'mineral-oils')->first();
        $syntheticOilCategory = Category::where('slug', 'synthetic-oils')->first();

        $products = [
            [
                'sku' => 'SF-MAX-20W50',
                'name' => 'SuperFuel Max 20W-50',
                'category_id' => $engineOilCategory->id,
                'subcategory_id' => $mineralOilCategory->id,
                'description' => 'High-performance mineral oil for heavy-duty engines',
                'price' => 4500.00,
                'cost_price' => 3500.00,
                'tax_rate' => 16.0,
                'unit' => 'Liter',
                'packaging' => '5L Container',
                'min_order_quantity' => 1,
                'reorder_level' => 30,
                'is_active' => true,
                'specifications' => json_encode([
                    'viscosity' => '20W-50',
                    'api_rating' => 'CI-4',
                    'base_oil' => 'Mineral',
                ]),
            ],
            [
                'sku' => 'ED-SYN-5W30',
                'name' => 'EcoDrive Synthetic 5W-30',
                'category_id' => $engineOilCategory->id,
                'subcategory_id' => $syntheticOilCategory->id,
                'description' => 'Fully synthetic oil for modern passenger vehicles',
                'price' => 7200.00,
                'cost_price' => 5800.00,
                'tax_rate' => 16.0,
                'unit' => 'Liter',
                'packaging' => '4L Container',
                'min_order_quantity' => 1,
                'reorder_level' => 40,
                'is_active' => true,
                'specifications' => json_encode([
                    'viscosity' => '5W-30',
                    'api_rating' => 'SN Plus',
                    'base_oil' => 'Full Synthetic',
                ]),
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}