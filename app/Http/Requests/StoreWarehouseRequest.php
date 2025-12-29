<?php

namespace App\Http\Requests\Warehouse;

use Illuminate\Foundation\Http\FormRequest;

class StoreWarehouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->role === 'Sales Manager';
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|unique:warehouses,code|max:10',
            'name' => 'required|string|max:255',
            'type' => 'required|in:Main,Regional,Outlet',
            'address' => 'required|string|max:500',
            'manager_email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'capacity' => 'required|integer|min:1',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'is_active' => 'boolean'
        ];
    }
}