<?php

namespace App\Http\Requests;

use App\Models\ClassRoom;
use Illuminate\Foundation\Http\FormRequest;

class StoreClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', ClassRoom::class);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:classes,name',
            'wali_kelas_id' => 'nullable|exists:users,id',
        ];
    }
}
