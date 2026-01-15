<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use App\Jobs\ProcessLowStockAlerts;

class LeysNotificationService
{
    /**
     * Create and send a notification.
     */
    public function createNotification(
        User $user,
        string $type,
        string $title,
        string $message,
        array $data = [],
        bool $sendEmail = true
    ): Notification {
        // Check user's notification preferences
        if (!$this->shouldSendNotification($user, $type)) {
            Log::info("User {$user->id} has disabled {$type} notifications");
            $sendEmail = false;
        }

        // Create notification record
        $notification = Notification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
        ]);

        // For low stock alerts, use ProcessLowStockAlerts job
        if ($sendEmail && $type === Notification::TYPE_LOW_STOCK) {
            ProcessLowStockAlerts::dispatch()->onQueue('notifications');
        }
        // For other notifications, you might want a different email job
        else if ($sendEmail) {
            // You can keep a generic email job here or remove email for now
            Log::info("Email notification would be sent for type: {$type}");
        }

        return $notification;
    }

    /**
     * Send order confirmation notification.
     */
    public function sendOrderConfirmation(User $user, array $orderData): Notification
    {
        $title = "Order Confirmation: {$orderData['order_number']}";
        $message = "Your order {$orderData['order_number']} has been confirmed successfully.";
        
        return $this->createNotification(
            $user,
            Notification::TYPE_ORDER_CONFIRMATION,
            $title,
            $message,
            $orderData
        );
    }

    /**
     * Send low stock alert.
     */
    public function sendLowStockAlert(User $user, array $productData): Notification
    {
        $title = "Low Stock Alert: {$productData['product_name']}";
        $message = "Product {$productData['product_name']} is below reorder level. Current stock: {$productData['current_stock']}";
        
        return $this->createNotification(
            $user,
            Notification::TYPE_LOW_STOCK,
            $title,
            $message,
            $productData
        );
    }

    /**
     * Send credit limit warning.
     */
    public function sendCreditLimitWarning(User $user, array $customerData): Notification
    {
        $title = "Credit Limit Warning: {$customerData['customer_name']}";
        $message = "Customer {$customerData['customer_name']} has reached {$customerData['usage_percentage']}% of credit limit.";
        
        return $this->createNotification(
            $user,
            Notification::TYPE_CREDIT_LIMIT_WARNING,
            $title,
            $message,
            $customerData
        );
    }

    /**
     * Send system announcement.
     */
    public function sendSystemAnnouncement(User $user, string $title, string $message): Notification
    {
        return $this->createNotification(
            $user,
            Notification::TYPE_SYSTEM_ANNOUNCEMENT,
            $title,
            $message,
            []
        );
    }

    /**
     * Mark all notifications as read for a user.
     */
    public function markAllAsRead(User $user): int
    {
        return $user->notifications()->unread()->update(['read_at' => now()]);
    }

    /**
     * Get unread notifications count for a user.
     */
    public function getUnreadCount(User $user): int
    {
        return $user->notifications()->unread()->count();
    }

    /**
     * Check if notification should be sent based on user preferences.
     */
    private function shouldSendNotification(User $user, string $type): bool
    {
        // Check user preferences (you can add this to users table)
        $preferences = $user->notification_preferences ?? [];
        
        // If preferences array is empty, send all notifications
        if (empty($preferences)) {
            return true;
        }
        
        // Check if this notification type is enabled in user preferences
        return in_array($type, $preferences);
    }
}