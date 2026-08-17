<?php

namespace App\Http\Requests\Stock;

use Illuminate\Foundation\Http\FormRequest;

class StockAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id'      => 'required|exists:products,id',
            'warehouse_id'    => 'required|exists:warehouses,id',
            'actual_quantity' => 'required|integer|min:0',
            'note'            => 'nullable|string',
        ];
    }
}
