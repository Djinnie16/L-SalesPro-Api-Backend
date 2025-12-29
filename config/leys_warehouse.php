<?php

return [
    'defaults' => [
        'reorder_level' => 30,
        'minimum_stock' => 10,
        'capacity_warning_threshold' => 80,
        'capacity_critical_threshold' => 90,
    ],
    
    'transfer' => [
        'default_status' => 'pending',
        'auto_approve' => false,
        'notification_emails' => [
            'warehouse_manager',
            'inventory_manager'
        ],
    ],
    
    'cache' => [
        'warehouse_list' => 3600, // 1 hour
        'inventory_data' => 1800, // 30 minutes
        'capacity_metrics' => 900, // 15 minutes
    ],
];