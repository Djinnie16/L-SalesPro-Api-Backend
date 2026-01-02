<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StockReservation;
use App\Repositories\OrderRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Exception;
use Carbon\Carbon;
use Illuminate\Support\Str;

class LeysOrderService
{
    protected OrderRepository $orderRepository;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function listOrders(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->orderRepository->getQuery()->with(['customer', 'user']);
        $query = $this->orderRepository->applyFilters($query, $filters);
        return $this->orderRepository->paginate($query, $perPage);
    }

    public function getOrderDetails(int $id): ?Order
    {
        return $this->orderRepository->findById($id);
    }

    public function createOrder(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $customer = Customer::findOrFail($data['customer_id']);
            
            // Step 1: Validate credit limit
            $preview = $this->calculateOrderTotals($data);
            if (!$customer->canPlaceOrder($preview['total_amount'])) {
                throw new Exception('Order exceeds customer credit limit.');
            }

            // Step 2-3: Check availability and reserve stock
            $reservations = [];
            foreach ($data['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                if ($product->total_available < $item['quantity']) {
                    throw new Exception("Insufficient stock for product {$product->name}.");
                }
                
                // Find warehouse with enough stock
                // Use raw calculation instead of virtual column
                $inventory = $product->inventory()
                    ->whereRaw('(quantity - reserved_quantity) >= ?', [$item['quantity']])
                    ->when(isset($item['warehouse_id']), fn($q) => $q->where('warehouse_id', $item['warehouse_id']))
                    ->first();
                
                if (!$inventory) {
                    throw new Exception("No warehouse has enough stock for product {$product->name}.");
                }
                
                // Reserve stock
                $reservation = StockReservation::create([
                    'product_id' => $product->id,
                    'warehouse_id' => $inventory->warehouse_id,
                    'quantity' => $item['quantity'],
                    'expires_at' => Carbon::now()->addMinutes(30),
                ]);
                $reservations[] = $reservation;
                
                // Update inventory reserved
                $inventory->increment('reserved_quantity', $item['quantity']);
            }

            // Step 4-6: Calculate totals and create order
            $orderData = [
                'order_number' => $this->generateOrderNumber(),
                'customer_id' => $data['customer_id'],
                'user_id' => auth()->id() ?? 1, // Fallback to user 1 if not authenticated
                'status' => 'pending',
                'subtotal' => $preview['subtotal'],
                'tax_amount' => $preview['tax_amount'],
                'discount_amount' => $preview['discount_amount'],
                'shipping_cost' => $data['shipping_cost'] ?? 0,
                'total_amount' => $preview['total_amount'],
                'discount_type' => $data['discount_type'] ?? null,
                'discount_value' => $data['discount_value'] ?? null,
                'notes' => $data['notes'] ?? null,
            ];
            
            $order = $this->orderRepository->create($orderData);

            // Create order items
            foreach ($data['items'] as $index => $item) {
                $product = Product::find($item['product_id']);
                $itemData = $this->calculateItemTotals($item, $product);
                
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'warehouse_id' => $reservations[$index]->warehouse_id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->price,
                    'tax_rate' => $product->tax_rate,
                    'tax_amount' => $itemData['tax_amount'],
                    'discount_amount' => $itemData['discount_amount'],
                    'discount_type' => $item['discount_type'] ?? null,
                    'discount_value' => $item['discount_value'] ?? null,
                    'total_price' => $itemData['total_price'],
                ]);
            }

            // Associate reservations with order
            foreach ($reservations as $reservation) {
                $reservation->update(['order_id' => $order->id]);
            }

            // Step 7: Queue confirmation email (use Laravel Queue)
            // dispatch(new SendOrderConfirmationEmail($order));

            // Update customer balance
            $customer->updateBalance($order->total_amount);

            return $order->fresh(['customer', 'user', 'items.product', 'items.warehouse']);
        });
    }

    public function updateOrderStatus(Order $order, array $data): Order
    {
        if (!$order->isCancellable() && $data['status'] === 'cancelled') {
            throw new Exception('Order cannot be cancelled after shipping.');
        }

        $updates = ['status' => $data['status']];
        switch ($data['status']) {
            case 'confirmed':
                $updates['confirmed_at'] = now();
                break;
            case 'shipped':
                $updates['shipped_at'] = now();
                break;
            case 'delivered':
                $updates['delivered_at'] = now();
                // Consume reservations
                foreach ($order->stockReservations as $reservation) {
                    $reservation->consume();
                }
                break;
            case 'cancelled':
                $updates['cancelled_at'] = now();
                $updates['cancelled_by'] = auth()->id() ?? 1;
                $updates['cancellation_reason'] = $data['cancellation_reason'] ?? null;
                // Release reservations
                foreach ($order->stockReservations as $reservation) {
                    $reservation->release();
                    $inventory = $reservation->product->inventory()
                        ->where('warehouse_id', $reservation->warehouse_id)
                        ->first();
                    if ($inventory) {
                        $inventory->decrement('reserved_quantity', $reservation->quantity);
                    }
                }
                // Refund customer balance
                $order->customer->decrement('current_balance', $order->total_amount);
                break;
        }

        $this->orderRepository->update($order, $updates);
        $order->refresh();

        return $order;
    }

    public function calculateOrderTotals(array $data): array
    {
        $subtotal = 0;
        $taxAmount = 0;
        $discountAmount = 0;

        foreach ($data['items'] as $item) {
            $product = Product::findOrFail($item['product_id']);
            $itemTotals = $this->calculateItemTotals($item, $product);
            $subtotal += $itemTotals['subtotal'];
            $taxAmount += $itemTotals['tax_amount'];
            $discountAmount += $itemTotals['discount_amount'];
        }

        // Apply order-level discount
        $orderDiscount = $this->calculateDiscount($subtotal, $data['discount_type'] ?? null, $data['discount_value'] ?? 0);
        $discountAmount += $orderDiscount;
        $taxableAmount = $subtotal - $discountAmount;
        $taxAmount = $this->calculateTax($taxableAmount, 16.0);

        $total = $taxableAmount + $taxAmount + ($data['shipping_cost'] ?? 0);

        return [
            'subtotal' => round($subtotal, 2),
            'discount_amount' => round($discountAmount, 2),
            'tax_amount' => round($taxAmount, 2),
            'shipping_cost' => round($data['shipping_cost'] ?? 0, 2),
            'total_amount' => round($total, 2),
        ];
    }

    private function calculateItemTotals(array $item, Product $product): array
    {
        $subtotal = $product->price * $item['quantity'];
        $discount = $this->calculateDiscount($subtotal, $item['discount_type'] ?? null, $item['discount_value'] ?? 0);
        $taxable = $subtotal - $discount;
        $tax = $this->calculateTax($taxable, $product->tax_rate);

        return [
            'subtotal' => round($subtotal, 2),
            'discount_amount' => round($discount, 2),
            'tax_amount' => round($tax, 2),
            'total_price' => round($taxable + $tax, 2),
        ];
    }

    private function calculateDiscount(float $amount, ?string $type, float $value): float
    {
        if (!$type) return 0;
        return $type === 'percentage' ? ($amount * $value / 100) : $value;
    }

    private function calculateTax(float $amount, float $rate): float
    {
        return $amount * ($rate / 100);
    }

    private function generateOrderNumber(): string
    {
        $date = Carbon::now();
        $prefix = 'ORD-' . $date->format('Y-m-');
        $lastOrder = Order::where('order_number', 'like', $prefix . '%')->latest('id')->first();
        $seq = $lastOrder ? (int) Str::afterLast($lastOrder->order_number, '-') + 1 : 1;
        return $prefix . str_pad($seq, 3, '0', STR_PAD_LEFT);
    }

    public function generateInvoiceData(Order $order): array
    {
        return [
            'order' => $order->toArray(),
            'items' => $order->items->toArray(),
            'customer' => $order->customer->toArray(),
        ];
    }

    public function cleanExpiredReservations(): void
    {
        $expired = StockReservation::where('status', 'reserved')
            ->where('expires_at', '<', now())
            ->get();
        
        foreach ($expired as $reservation) {
            $inventory = $reservation->product->inventory()
                ->where('warehouse_id', $reservation->warehouse_id)
                ->first();
            if ($inventory) {
                $inventory->decrement('reserved_quantity', $reservation->quantity);
            }
            $reservation->release();
        }
    }
}