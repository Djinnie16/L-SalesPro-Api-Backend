<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Seeder;

class NotificationsTableSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        
        foreach ($users as $user) {
            // Create sample notifications
            Notification::create([
                'user_id' => $user->id,
                'type' => Notification::TYPE_ORDER_CONFIRMATION,
                'title' => 'Order Confirmation: ORD-2024-01-001',
                'message' => 'Your order ORD-2024-01-001 has been confirmed successfully.',
                'data' => [
                    'order_number' => 'ORD-2024-01-001',
                    'total_amount' => 15000.00,
                    'status' => 'confirmed'
                ]
            ]);

            Notification::create([
                'user_id' => $user->id,
                'type' => Notification::TYPE_LOW_STOCK,
                'title' => 'Low Stock Alert: SuperFuel Max 20W-50',
                'message' => 'Product SuperFuel Max 20W-50 is below reorder level. Current stock: 25',
                'data' => [
                    'product_name' => 'SuperFuel Max 20W-50',
                    'sku' => 'SF-MAX-20W50',
                    'current_stock' => 25,
                    'reorder_level' => 30
                ]
            ]);

            // Mark some as read
            if ($user->id === 1) {
                Notification::create([
                    'user_id' => $user->id,
                    'type' => Notification::TYPE_SYSTEM_ANNOUNCEMENT,
                    'title' => 'System Maintenance Scheduled',
                    'message' => 'System maintenance is scheduled for this weekend.',
                    'read_at' => now(),
                    'data' => [
                        'maintenance_date' => '2024-01-15',
                        'duration' => '2 hours'
                    ]
                ]);
            }
        }
    }
}