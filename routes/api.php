<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\WarehouseController;
use App\Http\Controllers\Api\V1\StockTransferController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\NotificationController;

Route::prefix('v1')->group(function () {
    
    // Authentication Routes
    Route::prefix('auth')->group(function () {
        // Public routes (no authentication required)
        Route::post('/login', [AuthController::class, 'login'])
            ->middleware('throttle:5,1'); // 5 attempts per minute
        
        Route::post('/password/forgot', [AuthController::class, 'forgotPassword'])
            ->middleware('throttle:3,1'); // 3 attempts per minute
        
        Route::post('/password/reset', [AuthController::class, 'resetPassword'])
            ->middleware('throttle:3,1');
        
        // Protected routes (authentication required)
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/refresh', [AuthController::class, 'refresh']);
            Route::get('/user', [AuthController::class, 'user']);
        });
    });
    
    // Protected API routes
    Route::middleware('auth:sanctum')->group(function () {
        // Customer routes
        Route::apiResource('customers', CustomerController::class);
        Route::get('customers/{id}/orders', [CustomerController::class, 'orders']);
        Route::get('customers/{id}/credit-status', [CustomerController::class, 'creditStatus']);
        Route::get('customers/map-data', [CustomerController::class, 'mapData']);

        // Warehouse routes
        Route::apiResource('warehouses', WarehouseController::class);
        Route::get('warehouses/{id}/inventory', [WarehouseController::class, 'getInventory']);
        Route::get('warehouses/capacity-alerts', [WarehouseController::class, 'capacityAlerts']);
        
        // Stock transfer routes
        Route::apiResource('stock-transfers', StockTransferController::class)->except(['update', 'destroy']);
        Route::post('stock-transfers/{id}/approve', [StockTransferController::class, 'approve']);

        // Order routes
        Route::get('/orders', [OrderController::class, 'index']); 
        Route::get('/orders/{id}', [OrderController::class, 'show']); 
        Route::post('/orders', [OrderController::class, 'store']); 
        Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus']); 
        Route::get('/orders/{id}/invoice', [OrderController::class, 'generateInvoice']); 
        Route::post('/orders/calculate-total', [OrderController::class, 'calculateTotal']); 

        //Dashboard routes
       Route::prefix('dashboard')->group(function () {
            Route::get('/summary', [DashboardController::class, 'getSummary']);
            Route::get('/sales-performance', [DashboardController::class, 'getSalesPerformance']);
            Route::get('/inventory-status', [DashboardController::class, 'getInventoryStatus']);
            Route::get('/top-products', [DashboardController::class, 'getTopProducts']);
        });

        //Notification routes
        Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::put('{notification}/read', [NotificationController::class, 'markAsRead']);
        Route::put('read-all', [NotificationController::class, 'markAllAsRead']);
        Route::delete('{notification}', [NotificationController::class, 'destroy']);
        Route::get('unread-count', [NotificationController::class, 'unreadCount']);
    });
    });
});