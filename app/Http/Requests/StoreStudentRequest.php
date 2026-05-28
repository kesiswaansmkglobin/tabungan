<?php

namespace App\Http\Requests;

use App\Models\Student;
use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Student::class);
    }

    public function rules(): array
    {
        return [
            'nis' => 'required|string|max:20|unique:students,nis',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20|regex:/^(\+62|62|0)8[0-9]{7,12}$/',
            'password' => 'nullable|string|min:4|max:255',
            'class_id' => 'required|exists:classes,id',
        ];
    }
}
