<?php

namespace App\Http\Requests\Cash;

use Illuminate\Foundation\Http\FormRequest;

class CashMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cash_register_id' => 'required|exists:cash_registers,id',
            'amount'           => 'required|numeric|min:1',
            'category'         => 'nullable|string',
            'description'      => 'nullable|string',
        ];
    }
}
