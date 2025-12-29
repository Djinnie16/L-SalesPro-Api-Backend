<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create_customers'); // Assume policy/gate
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'category' => 'required|in:A,A+,B,C',
            'contact_person' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|unique:customers,email',
            'tax_id' => 'required|string|unique:customers,tax_id|max:50',
            'payment_terms' => 'required|integer|min:0',
            'credit_limit' => 'required|numeric|min:0',
            'current_balance' => 'numeric|min:0',
            'territory' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'address' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'category.in' => 'Category must be one of: A, A+, B, C.',
            // Add more field-specific messages
        ];
    }
}