<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StockTransfer\StoreStockTransferRequest;
use App\Http\Requests\StockTransfer\ApproveStockTransferRequest;
use App\Http\Resources\StockTransferResource;
use App\Http\Resources\StockTransferCollection;
use App\Services\LeysWarehouseService;
use App\Repositories\StockTransferRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockTransferController extends Controller
{
    public function __construct(
        private StockTransferRepository $stockTransferRepository,
        private LeysWarehouseService $warehouseService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/v1/stock-transfers",
     *     summary="List stock transfers",
     *     tags={"Stock Transfers"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status",
     *         @OA\Schema(type="string", enum={"pending", "approved", "in_transit", "completed", "cancelled"})
     *     ),
     *     @OA\Parameter(
     *         name="source_warehouse_id",
     *         in="query",
     *         description="Filter by source warehouse",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="destination_warehouse_id",
     *         in="query",
     *         description="Filter by destination warehouse",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of stock transfers",
     *         @OA\JsonContent(ref="#/components/schemas/StockTransferCollection")
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $transfers = $this->stockTransferRepository->getAllTransfers($request->all());
        
        return response()->json([
            'success' => true,
            'data' => new StockTransferCollection($transfers),
            'message' => 'Stock transfers retrieved successfully'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/stock-transfers",
     *     summary="Transfer stock between warehouses",
     *     tags={"Stock Transfers"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreStockTransferRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Stock transfer initiated",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/StockTransferResource")
     *         )
     *     )
     * )
     */
    public function store(StoreStockTransferRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['initiated_by'] = $request->user()->id;
            
            $stockTransfer = $this->warehouseService->transferStock($data);
            
            return response()->json([
                'success' => true,
                'data' => new StockTransferResource($stockTransfer),
                'message' => 'Stock transfer initiated successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/stock-transfers/{id}/approve",
     *     summary="Approve stock transfer",
     *     tags={"Stock Transfers"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Transfer ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ApproveStockTransferRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Stock transfer approved and processed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/StockTransferResource")
     *         )
     *     )
     * )
     */
    public function approve(int $id, ApproveStockTransferRequest $request): JsonResponse
    {
        try {
            $stockTransfer = $this->warehouseService->approveStockTransfer($id, $request->user()->id);
            
            return response()->json([
                'success' => true,
                'data' => new StockTransferResource($stockTransfer),
                'message' => 'Stock transfer approved and processed successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/stock-transfers/{id}",
     *     summary="Get stock transfer details",
     *     tags={"Stock Transfers"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Transfer ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Stock transfer details",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/StockTransferResource")
     *         )
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        $transfer = $this->stockTransferRepository->getStockTransferById($id);
        
        if (!$transfer) {
            return response()->json([
                'success' => false,
                'message' => 'Stock transfer not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new StockTransferResource($transfer),
            'message' => 'Stock transfer retrieved successfully'
        ]);
    }
}