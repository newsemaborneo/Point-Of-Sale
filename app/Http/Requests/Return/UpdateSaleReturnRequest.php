<?php

namespace App\Http\Requests\Return;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSaleReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reason'        => 'nullable|string',
            'refund_method' => 'required|in:cash,store_credit,bank_transfer',
        ];
    }
}
