<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductCollection;
use App\Models\Product;
use App\Services\LeysProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(private LeysProductService $productService) {}

    public function index(Request $request): JsonResponse
    {
        $products = $this->productService->getProductsWithFilters($request->all());
        
        return response()->json([
            'success' => true,
            'data' => new ProductCollection($products),
            'message' => 'Products retrieved successfully'
        ]);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->productService->createProduct($request->validated());
        
        return response()->json([
            'success' => true,
            'data' => new ProductResource($product),
            'message' => 'Product created successfully'
        ], 201);
    }

    public function show(Product $product): JsonResponse
    {
        $product->load(['inventories.warehouse']);
        
        return response()->json([
            'success' => true,
            'data' => new ProductResource($product),
            'message' => 'Product retrieved successfully'
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $updated = $this->productService->updateProduct($product, $request->validated());
        
        return response()->json([
            'success' => true,
            'data' => new ProductResource($product->fresh()),
            'message' => 'Product updated successfully'
        ]);
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully'
        ]);
    }

    public function getLowStockProducts(Request $request): JsonResponse
    {
        $products = $this->productService->getLowStockProducts($request->all());
        
        return response()->json([
            'success' => true,
            'data' => new ProductCollection($products),
            'message' => 'Low stock products retrieved successfully'
        ]);
    }
}