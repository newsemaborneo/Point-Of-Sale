<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('product')?->id;

        return [
            'category_id'    => 'nullable|exists:categories,id',
            'unit_id'        => 'nullable|exists:units,id',
            'supplier_id'    => 'nullable|exists:suppliers,id',
            'name'           => 'sometimes|string|max:255',
            'sku'            => 'sometimes|string|unique:products,sku,' . $productId,
            'barcode'        => 'nullable|string|unique:products,barcode,' . $productId,
            'description'    => 'nullable|string',
            'photo'          => 'nullable|image|max:2048',
            'purchase_price' => 'sometimes|numeric|min:0',
            'sale_price'     => 'sometimes|numeric|min:0',
            'discount_type'  => 'nullable|in:percent,nominal',
            'discount_value' => 'nullable|numeric|min:0',
            'tax_percent'    => 'nullable|numeric|min:0',
            'min_stock'      => 'nullable|integer|min:0',
            'is_active'      => 'boolean',
            'has_expiry'     => 'boolean',
            'expired_date'   => 'nullable|date',
        ];
    }
}
