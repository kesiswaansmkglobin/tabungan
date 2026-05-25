<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateTierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage-gamification');
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
