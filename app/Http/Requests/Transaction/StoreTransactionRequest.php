<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
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
            'items.*.price'      => 'required|numeric',
            'customer_id'        => 'nullable|exists:customers,id',
            'payment_method'     => 'required|string',
            'paid_amount'        => 'required|numeric',
            'subtotal'           => 'required|numeric',
            'discount_total'     => 'required|numeric',
            'grand_total'        => 'required|numeric',
            'voucher_code'       => 'nullable|string',
        ];
    }
}
