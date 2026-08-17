<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:255',
            'phone'       => 'nullable|string',
            'email'       => 'nullable|email',
            'address'        => 'nullable|string',
            'member_type_id' => 'nullable|exists:member_types,id',
        ];
    }
}
