<?php

namespace App\Http\Requests;

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
            'student_id' => 'required|exists:students,id',
            'type' => 'required|in:setor,tarik',
            'amount' => 'required|integer|min:1',
            'transaction_date' => 'required|date',
            'note' => 'nullable|string|max:500',
        ];
    }
}
