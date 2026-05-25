<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('student'));
    }

    public function rules(): array
    {
        $student = $this->route('student');

        return [
            'nis' => 'required|string|max:20|unique:students,nis,'.$student->id,
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20|regex:/^(\+62|62|0)8[0-9]{7,12}$/',
            'class_id' => 'required|exists:classes,id',
        ];
    }
}
