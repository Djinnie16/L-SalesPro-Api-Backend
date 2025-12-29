<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\CustomerCollection;
use App\Http\Resources\OrderCollection; // Assume OrderResource exists
use App\Services\LeysCustomerService;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    protected $service;

    public function __construct(LeysCustomerService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $customers = $this->service->listPaginated($perPage);
        return $this->successResponse(new CustomerCollection($customers), 'Customers retrieved successfully');
    }

    public function show(int $id): \Illuminate\Http\JsonResponse
    {
        $customer = $this->service->getDetails($id);
        return $this->successResponse(new CustomerResource($customer), 'Customer details retrieved');
    }

    public function store(CreateCustomerRequest $request): \Illuminate\Http\JsonResponse
    {
        $customer = $this->service->create($request->validated());
        return $this->successResponse(new CustomerResource($customer), 'Customer created successfully', 201);
    }

    public function update(UpdateCustomerRequest $request, int $id): \Illuminate\Http\JsonResponse
    {
        $customer = $this->service->update($id, $request->validated());
        return $this->successResponse(new CustomerResource($customer), 'Customer updated successfully');
    }

    public function destroy(int $id): \Illuminate\Http\JsonResponse
    {
        $this->service->delete($id);
        return $this->successResponse(null, 'Customer deleted successfully', 204);
    }

    public function orders(int $id, Request $request): \Illuminate\Http\JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $orders = $this->service->getOrders($id, $perPage);
        return $this->successResponse(new OrderCollection($orders), 'Customer orders retrieved'); // Assume OrderCollection
    }

    public function creditStatus(int $id): \Illuminate\Http\JsonResponse
    {
        $status = $this->service->getCreditStatus($id);
        return $this->successResponse($status, 'Credit status retrieved');
    }

    public function mapData(): \Illuminate\Http\JsonResponse
    {
        $mapData = $this->service->getMapData();
        return $this->successResponse($mapData, 'Customer map data retrieved');
    }

    // Helper for consistent responses
    protected function successResponse($data, string $message, int $code = 200): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message,
            'errors' => [],
            'meta' => [],
        ], $code);
    }

    // Override for errors in exception handler if needed
}