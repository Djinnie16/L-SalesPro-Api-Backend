<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\CreateOrderRequest;
use App\Http\Requests\V1\UpdateOrderStatusRequest;
use App\Http\Requests\V1\CalculateTotalRequest;
use App\Http\Resources\V1\OrderResource;
use App\Services\LeysOrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected LeysOrderService $orderService;

    public function __construct(LeysOrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['status', 'customer_id', 'date_from', 'date_to']);
        $orders = $this->orderService->listOrders($filters, $request->get('per_page', 15));
        return OrderResource::collection($orders);
    }

    public function show(int $id)
    {
        $order = $this->orderService->getOrderDetails($id);
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }
        return new OrderResource($order);
    }

    public function store(CreateOrderRequest $request)
    {
        try {
            $order = $this->orderService->createOrder($request->validated());
            return (new OrderResource($order))->response()->setStatusCode(201);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function updateStatus(int $id, UpdateOrderStatusRequest $request)
    {
        $order = $this->orderService->getOrderDetails($id);
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }
        try {
            $updatedOrder = $this->orderService->updateOrderStatus($order, $request->validated());
            return new OrderResource($updatedOrder);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function generateInvoice(int $id)
    {
        $order = $this->orderService->getOrderDetails($id);
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }
        return response()->json($this->orderService->generateInvoiceData($order));
    }

    public function calculateTotal(CalculateTotalRequest $request)
    {
        try {
            return response()->json($this->orderService->calculateOrderTotals($request->validated()));
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}