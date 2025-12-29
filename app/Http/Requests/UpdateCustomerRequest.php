<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update_customers');
    }

    public function rules(): array
    {
        $customerId = $this->route('id');
        return [
            'name' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|string|max:100',
            'category' => 'sometimes|required|in:A,A+,B,C',
            'contact_person' => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|required|string|max:20',
            'email' => ['sometimes', 'required', 'email', Rule::unique('customers')->ignore($customerId)],
            'tax_id' => ['sometimes', 'required', 'string', 'max:50', Rule::unique('customers')->ignore($customerId)],
            'payment_terms' => 'sometimes|required|integer|min:0',
            'credit_limit' => 'sometimes|required|numeric|min:0',
            'current_balance' => 'sometimes|numeric|min:0',
            'territory' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'address' => 'nullable|string',
        ];
    }
}