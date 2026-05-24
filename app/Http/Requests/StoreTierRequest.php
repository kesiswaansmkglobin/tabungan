<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'min_balance' => 'required|integer|min:0',
            'icon' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:50',
            'order_index' => 'required|integer|min:0',
        ];
    }
}
