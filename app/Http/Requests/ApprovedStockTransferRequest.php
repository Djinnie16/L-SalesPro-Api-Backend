<?php

namespace App\Http\Requests\StockTransfer;

use Illuminate\Foundation\Http\FormRequest;

class ApproveStockTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->role === 'Sales Manager';
    }

    public function rules(): array
    {
        return [];
    }
}