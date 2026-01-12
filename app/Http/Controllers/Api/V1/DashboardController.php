<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\DashboardSummaryResource;
use App\Http\Resources\SalesPerformanceResource;
use App\Http\Resources\InventoryStatusResource;
use App\Http\Resources\TopProductsResource;
use App\Services\LeysDashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $dashboardService;
    
    public function __construct(LeysDashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }
    
    /**
     * @OA\Get(
     *     path="/api/dashboard/summary",
     *     summary="Get overall sales metrics",
     *     tags={"Dashboard"},
     *     security={{ "sanctum": {} }},
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Start date (Y-m-d)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="End date (Y-m-d)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="Dashboard summary retrieved")
     *         )
     *     )
     * )
     */
    public function getSummary(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        
        // Validate date format if provided
        if ($startDate && !strtotime($startDate)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid start date format. Use YYYY-MM-DD.'
            ], 422);
        }
        
        if ($endDate && !strtotime($endDate)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid end date format. Use YYYY-MM-DD.'
            ], 422);
        }
        
        $summary = $this->dashboardService->getSummaryMetrics($startDate, $endDate);
        
        return response()->json([
            'success' => true,
            'data' => new DashboardSummaryResource($summary),
            'message' => 'Dashboard summary retrieved successfully'
        ]);
    }
    
    /**
     * @OA\Get(
     *     path="/api/dashboard/sales-performance",
     *     summary="Get sales performance data",
     *     tags={"Dashboard"},
     *     security={{ "sanctum": {} }},
     *     @OA\Parameter(
     *         name="period",
     *         in="query",
     *         description="Period filter (today, week, month, quarter, year)",
     *         required=false,
     *         @OA\Schema(type="string", default="month")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success"
     *     )
     * )
     */
    public function getSalesPerformance(Request $request)
    {
        $validPeriods = ['today', 'week', 'month', 'quarter', 'year'];
        $period = $request->query('period', 'month');
        
        if (!in_array($period, $validPeriods)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid period. Allowed: ' . implode(', ', $validPeriods)
            ], 422);
        }
        
        $performance = $this->dashboardService->getSalesPerformance($period);
        
        return response()->json([
            'success' => true,
            'data' => new SalesPerformanceResource($performance),
            'message' => 'Sales performance data retrieved'
        ]);
    }
    
    /**
     * @OA\Get(
     *     path="/api/dashboard/inventory-status",
     *     summary="Get category-wise inventory summary",
     *     tags={"Dashboard"},
     *     security={{ "sanctum": {} }},
     *     @OA\Response(
     *         response=200,
     *         description="Success"
     *     )
     * )
     */
    public function getInventoryStatus()
    {
        $inventoryStatus = $this->dashboardService->getInventoryStatus();
        
        return response()->json([
            'success' => true,
            'data' => new InventoryStatusResource($inventoryStatus),
            'message' => 'Inventory status retrieved'
        ]);
    }
    
    /**
     * @OA\Get(
     *     path="/api/dashboard/top-products",
     *     summary="Get top 5 selling products",
     *     tags={"Dashboard"},
     *     security={{ "sanctum": {} }},
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Number of top products (default: 5)",
     *         required=false,
     *         @OA\Schema(type="integer", default=5)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success"
     *     )
     * )
     */
    public function getTopProducts(Request $request)
    {
        $limit = $request->query('limit', 5);
        
        if ($limit < 1 || $limit > 50) {
            return response()->json([
                'success' => false,
                'message' => 'Limit must be between 1 and 50'
            ], 422);
        }
        
        $topProducts = $this->dashboardService->getTopProducts($limit);
        
        return response()->json([
            'success' => true,
            'data' => new TopProductsResource($topProducts),
            'message' => 'Top products retrieved'
        ]);
    }
}