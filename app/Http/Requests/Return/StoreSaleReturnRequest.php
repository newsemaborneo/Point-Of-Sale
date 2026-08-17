<?php

namespace App\Http\Requests\Return;

use Illuminate\Foundation\Http\FormRequest;

class StoreSaleReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
            'reason'             => 'nullable|string',
            'refund_method'      => 'required|in:cash,store_credit,bank_transfer',
        ];
    }
}
