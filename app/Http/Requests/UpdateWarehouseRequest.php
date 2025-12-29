<?php

namespace App\Http\Requests\Warehouse;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWarehouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->role === 'Sales Manager';
    }

    public function rules(): array
    {
        $warehouseId = $this->route('id');

        return [
            'code' => 'sometimes|string|unique:warehouses,code,' . $warehouseId . '|max:10',
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:Main,Regional,Outlet',
            'address' => 'sometimes|string|max:500',
            'manager_email' => 'sometimes|email|max:255',
            'phone' => 'sometimes|string|max:20',
            'capacity' => 'sometimes|integer|min:1',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'is_active' => 'sometimes|boolean'
        ];
    }
}
