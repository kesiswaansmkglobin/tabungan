<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $class = $this->route('class');

        return [
            'name' => 'required|string|max:255|unique:classes,name,'.$class->id,
            'wali_kelas_id' => 'nullable|exists:users,id',
        ];
    }
}
