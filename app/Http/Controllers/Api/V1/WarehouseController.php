<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Warehouse\StoreWarehouseRequest;
use App\Http\Requests\Warehouse\UpdateWarehouseRequest;
use App\Http\Resources\WarehouseResource;
use App\Http\Resources\WarehouseCollection;
use App\Http\Resources\InventoryCollection;
use App\Services\LeysWarehouseService;
use App\Repositories\WarehouseRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function __construct(
        private WarehouseRepository $warehouseRepository,
        private LeysWarehouseService $warehouseService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/v1/warehouses",
     *     summary="List all warehouses",
     *     tags={"Warehouses"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filter by warehouse type",
     *         @OA\Schema(type="string", enum={"Main", "Regional", "Outlet"})
     *     ),
     *     @OA\Parameter(
     *         name="is_active",
     *         in="query",
     *         description="Filter by active status",
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search in code, name, or address",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of warehouses",
     *         @OA\JsonContent(ref="#/components/schemas/WarehouseCollection")
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $warehouses = $this->warehouseRepository->getAllWarehouses($request->all());
        
        return response()->json([
            'success' => true,
            'data' => new WarehouseCollection($warehouses),
            'message' => 'Warehouses retrieved successfully'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/warehouses/{id}/inventory",
     *     summary="Get warehouse-specific inventory",
     *     tags={"Warehouses"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Warehouse ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="low_stock",
     *         in="query",
     *         description="Filter low stock items",
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="out_of_stock",
     *         in="query",
     *         description="Filter out of stock items",
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Warehouse inventory",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object", ref="#/components/schemas/InventoryCollection")
     *         )
     *     )
     * )
     */
    public function getInventory(int $id, Request $request): JsonResponse
    {
        $inventory = $this->warehouseRepository->getWarehouseInventory($id, $request->all());
        
        return response()->json([
            'success' => true,
            'data' => new InventoryCollection($inventory),
            'message' => 'Warehouse inventory retrieved successfully'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/warehouses/{id}",
     *     summary="Get warehouse details",
     *     tags={"Warehouses"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Warehouse ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Warehouse details",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/WarehouseResource")
     *         )
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        $warehouse = $this->warehouseRepository->getWarehouseById($id);
        
        if (!$warehouse) {
            return response()->json([
                'success' => false,
                'message' => 'Warehouse not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new WarehouseResource($warehouse),
            'message' => 'Warehouse retrieved successfully'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/warehouses",
     *     summary="Create a new warehouse",
     *     tags={"Warehouses"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreWarehouseRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Warehouse created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/WarehouseResource")
     *         )
     *     )
     * )
     */
    public function store(StoreWarehouseRequest $request): JsonResponse
    {
        $warehouse = $this->warehouseRepository->createWarehouse($request->validated());
        
        return response()->json([
            'success' => true,
            'data' => new WarehouseResource($warehouse),
            'message' => 'Warehouse created successfully'
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/warehouses/{id}",
     *     summary="Update warehouse",
     *     tags={"Warehouses"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Warehouse ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateWarehouseRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Warehouse updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/WarehouseResource")
     *         )
     *     )
     * )
     */
    public function update(UpdateWarehouseRequest $request, int $id): JsonResponse
    {
        $updated = $this->warehouseRepository->updateWarehouse($id, $request->validated());
        
        if (!$updated) {
            return response()->json([
                'success' => false,
                'message' => 'Warehouse not found or update failed'
            ], 404);
        }

        $warehouse = $this->warehouseRepository->getWarehouseById($id);
        
        return response()->json([
            'success' => true,
            'data' => new WarehouseResource($warehouse),
            'message' => 'Warehouse updated successfully'
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/warehouses/{id}",
     *     summary="Delete warehouse",
     *     tags={"Warehouses"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Warehouse ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Warehouse deleted successfully"
     *     )
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->warehouseRepository->deleteWarehouse($id);
        
        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Warehouse not found or delete failed'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Warehouse deleted successfully'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/warehouses/capacity-alerts",
     *     summary="Get warehouse capacity alerts",
     *     tags={"Warehouses"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Capacity alerts",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="warehouse", type="string"),
     *                     @OA\Property(property="code", type="string"),
     *                     @OA\Property(property="utilization_rate", type="number"),
     *                     @OA\Property(property="message", type="string"),
     *                     @OA\Property(property="severity", type="string", enum={"low", "medium", "high"})
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function capacityAlerts(): JsonResponse
    {
        $alerts = $this->warehouseService->getCapacityAlerts();
        
        return response()->json([
            'success' => true,
            'data' => $alerts,
            'message' => 'Capacity alerts retrieved successfully'
        ]);
    }
}