<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->role === 'Sales Manager';
    }

    public function rules(): array
    {
        return [
            'sku' => 'required|string|unique:products,sku|max:50',
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'subcategory' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
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