<?php

namespace App\Http\Requests\Cash;

use Illuminate\Foundation\Http\FormRequest;

class OpenCashRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'branch_id'       => 'nullable|exists:branches,id',
            'opening_balance' => 'required|numeric|min:0',
            'user_id'         => 'nullable|exists:users,id',
        ];
    }
}
