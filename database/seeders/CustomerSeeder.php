<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Category;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $categoryA = Category::where('slug', 'a')->first();
        $categoryAPlus = Category::where('slug', 'a-plus')->first();

        $customers = [
            [
                'code' => 'CUST-1001',
                'name' => 'Quick Auto Services Ltd',
                'type' => 'Garage',
                'category' => $categoryA->name,
                'contact_person' => 'John Mwangi',
                'phone' => '+254-712-345678',
                'email' => 'info@quickautoservices.co.ke',
                'tax_id' => 'P051234567Q',
                'payment_terms' => 30,
                'credit_limit' => 500000.00,
                'current_balance' => 120000.00,
                'latitude' => -1.319370,
                'longitude' => 36.824120,
                'address' => 'Mombasa Road, Auto Plaza Building, Nairobi',
                'territory' => 'Nairobi Central',
                'is_active' => true,
            ],
            [
                'code' => 'CUST-1002',
                'name' => 'Premium Motors Kenya',
                'type' => 'Dealership',
                'category' => $categoryAPlus->name,
                'contact_person' => 'Sarah Wanjiku',
                'phone' => '+254-722-678901',
                'email' => 'sarah.w@premiummotors.co.ke',
                'tax_id' => 'P051345678R',
                'payment_terms' => 45,
                'credit_limit' => 1000000.00,
                'current_balance' => 450000.00,
                'latitude' => -1.292066,
                'longitude' => 36.821946,
                'address' => 'Uhuru Highway, Premium Towers, Nairobi',
                'territory' => 'Nairobi West',
                'is_active' => true,
            ],
        ];

        foreach ($customers as $customer) {
            Customer::create($customer);
        }
    }
}