<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('transaction'));
    }

    public function rules(): array
    {
        return [
            'amount' => 'required|integer|min:1',
            'transaction_date' => 'required|date',
            'note' => 'nullable|string|max:500',
        ];
    }
}
