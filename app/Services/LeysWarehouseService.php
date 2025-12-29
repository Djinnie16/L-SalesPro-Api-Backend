<?php

namespace App\Services;

use App\Repositories\WarehouseRepository;
use App\Repositories\StockTransferRepository;
use App\Repositories\InventoryRepository;
use App\Models\StockTransfer;
use Illuminate\Support\Facades\DB;
use App\Notifications\StockTransferNotification;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\WarehouseCapacityException;

class LeysWarehouseService
{
    public function __construct(
        private WarehouseRepository $warehouseRepository,
        private StockTransferRepository $stockTransferRepository,
        private InventoryRepository $inventoryRepository
    ) {}

    /**
     * Transfer stock between warehouses
     */
    public function transferStock(array $transferData): StockTransfer
    {
        return DB::transaction(function () use ($transferData) {
            // Validate source warehouse has sufficient stock
            $sourceInventory = $this->inventoryRepository->getInventoryByProductAndWarehouse(
                $transferData['product_id'],
                $transferData['from_warehouse_id']
            );


            if (!$sourceInventory || $sourceInventory->available_quantity < $transferData['quantity']) {
                throw new InsufficientStockException('Insufficient stock in source warehouse');
            }

            // Validate destination warehouse has capacity
            $destinationWarehouse = $this->warehouseRepository
                ->getWarehouseById($transferData['to_warehouse_id']); // Changed from 'destination_warehouse_id'

            if (!$destinationWarehouse->hasCapacity($transferData['quantity'])) {
                throw new WarehouseCapacityException('Destination warehouse has insufficient capacity');
            }

            // Generate transfer number
            $transferData['transfer_number'] = $this->generateTransferNumber();
            $transferData['status'] = StockTransfer::STATUS_PENDING;
            $transferData['initiated_by'] = auth()->id();

            // Create stock transfer record
            $stockTransfer = $this->stockTransferRepository->createStockTransfer($transferData);

            // Reserve stock in source warehouse
            $this->inventoryRepository->reserveStock(
            $transferData['product_id'],
            $transferData['from_warehouse_id'],
            $transferData['quantity'],
            'transfer_' . $stockTransfer->id
            );

            // Send notification
            $this->sendTransferNotification($stockTransfer);

            return $stockTransfer;
        });
    }

    /**
     * Approve and process stock transfer
     */
    public function approveStockTransfer(int $transferId, int $approvedBy): StockTransfer
    {
        return DB::transaction(function () use ($transferId, $approvedBy) {
            $stockTransfer = $this->stockTransferRepository->getStockTransferById($transferId);
            
            if (!$stockTransfer || $stockTransfer->status !== StockTransfer::STATUS_PENDING) {
                throw new \Exception('Transfer not found or not pending');
            }

            // Update transfer status
            $stockTransfer->update([
                'status' => StockTransfer::STATUS_APPROVED,
                'transferred_by' => $approvedBy,
                'transferred_at' => now()
            ]);

            // Move stock from source to destination
            $this->processStockMovement($stockTransfer);

            // Update transfer status to completed
            $stockTransfer->update(['status' => StockTransfer::STATUS_COMPLETED]);

            return $stockTransfer->fresh();
        });
    }

    /**
     * Process the actual stock movement
     */
    private function processStockMovement(StockTransfer $stockTransfer): void
    {
        // Deduct from source warehouse
        $this->inventoryRepository->adjustStock(
            $stockTransfer->product_id,
            $stockTransfer->source_warehouse_id,
            -$stockTransfer->quantity,
            'stock_transfer',
            $stockTransfer->id
        );

        // Release reservation
        $this->inventoryRepository->releaseReservedStock(
            $stockTransfer->product_id,
            $stockTransfer->source_warehouse_id,
            $stockTransfer->quantity,
            'transfer_' . $stockTransfer->id
        );

        // Add to destination warehouse
        $this->inventoryRepository->adjustStock(
            $stockTransfer->product_id,
            $stockTransfer->destination_warehouse_id,
            $stockTransfer->quantity,
            'stock_transfer',
            $stockTransfer->id
        );
    }

    /**
     * Generate unique transfer number
     */
    private function generateTransferNumber(): string
    {
        return 'TF-' . now()->format('Y-m') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
    }

    /**
     * Send notification for stock transfer
     */
    private function sendTransferNotification(StockTransfer $stockTransfer): void
    {
        $sourceManager = $stockTransfer->sourceWarehouse->manager_email;
        $destinationManager = $stockTransfer->destinationWarehouse->manager_email;

        // Send notifications (this would be queued in production)
        \Notification::route('mail', $sourceManager)
            ->notify(new StockTransferNotification($stockTransfer, 'source'));

        \Notification::route('mail', $destinationManager)
            ->notify(new StockTransferNotification($stockTransfer, 'destination'));
    }

    /**
     * Get warehouse capacity alerts
     */
    public function getCapacityAlerts(): array
    {
        $warehouses = $this->warehouseRepository->getAllWarehouses();
        $alerts = [];

        foreach ($warehouses as $warehouse) {
            $metrics = $this->warehouseRepository->getWarehouseCapacityMetrics($warehouse->id);
            
            if ($metrics['utilization_rate'] > 90) {
                $alerts[] = [
                    'warehouse' => $warehouse->name,
                    'code' => $warehouse->code,
                    'utilization_rate' => $metrics['utilization_rate'],
                    'message' => 'Warehouse capacity utilization is above 90%',
                    'severity' => 'high'
                ];
            } elseif ($metrics['utilization_rate'] > 80) {
                $alerts[] = [
                    'warehouse' => $warehouse->name,
                    'code' => $warehouse->code,
                    'utilization_rate' => $metrics['utilization_rate'],
                    'message' => 'Warehouse capacity utilization is above 80%',
                    'severity' => 'medium'
                ];
            }
        }

        return $alerts;
    }
}