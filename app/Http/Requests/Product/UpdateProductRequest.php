<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->role === 'Sales Manager';
    }

    public function rules(): array
    {
        $productId = $this->route('product');

        return [
            'sku' => 'sometimes|string|unique:products,sku,' . $productId . '|max:50',
            'name' => 'sometimes|string|max:255',
            'category' => 'sometimes|string|max:100',
            'subcategory' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'unit' => 'nullable|string|max:50',
            'packaging' => 'nullable|string|max:100',
            'min_order_quantity' => 'nullable|integer|min:1',
            'reorder_level' => 'nullable|integer|min:0',
            'specifications' => 'nullable|array',
            'specifications.*.key' => 'required_with:specifications|string',
            'specifications.*.value' => 'required_with:specifications|string',
            'is_active' => 'nullable|boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'sku.unique' => 'This SKU already exists in the system.',
            'specifications.*.key.required_with' => 'Each specification must have a key.',
            'specifications.*.value.required_with' => 'Each specification must have a value.',
        ];
    }
}