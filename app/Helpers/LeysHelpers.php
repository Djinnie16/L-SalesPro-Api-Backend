<?php

namespace App\Helpers;

class LeysHelpers
{
    /**
     * Format currency amount
     */
    public static function formatCurrency(float $amount): string
    {
        return 'KES ' . number_format($amount, 2) . ' /=';
    }

    /**
     * Generate order number
     */
    public static function generateOrderNumber(): string
    {
        $prefix = 'ORD-' . date('Y-m');
        $lastOrder = \App\Models\Order::where('order_number', 'like', $prefix . '-%')
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastOrder) {
            $lastNumber = (int) substr($lastOrder->order_number, -3);
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }
        
        return $prefix . '-' . $newNumber;
    }

    /**
     * Calculate tax amount
     */
    public static function calculateTax(float $amount, float $rate): float
    {
        return $amount * ($rate / 100);
    }

    /**
     * Generate transfer number
     */
    public static function generateTransferNumber(): string
    {
        $date = now()->format('Y-m');
        $random = str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        
        return "TF-{$date}-{$random}";
    }

    /**
     * Calculate warehouse utilization percentage
     */
    public static function calculateUtilization(int $used, int $total): float
    {
        if ($total <= 0) return 0;
        return round(($used / $total) * 100, 2);
    }
}