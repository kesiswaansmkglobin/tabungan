<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nis' => 'required|string|max:20|unique:students,nis',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'class_id' => 'required|exists:classes,id',
        ];
    }
}
