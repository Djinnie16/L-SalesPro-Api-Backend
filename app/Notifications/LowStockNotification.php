<?php

namespace App\Notifications;

use App\Models\Inventory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private Inventory $inventory) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('🚨 Low Stock Alert - ' . $this->inventory->product->name)
            ->line('Low stock alert for product: ' . $this->inventory->product->name)
            ->line('SKU: ' . $this->inventory->product->sku)
            ->line('Warehouse: ' . $this->inventory->warehouse->name)
            ->line('Current Stock: ' . $this->inventory->available_quantity)
            ->line('Reorder Level: ' . $this->inventory->reorder_level)
            ->action('View Product', url('/products/' . $this->inventory->product_id))
            ->line('Please reorder stock to avoid stockouts.');
    }
}