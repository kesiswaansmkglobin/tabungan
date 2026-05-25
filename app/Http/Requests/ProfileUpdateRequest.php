<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
                function ($attribute, $value, $fail) {
                    if ($value !== $this->user()->email) {
                        $password = $this->input('current_password');
                        if (empty($password)) {
                            $fail('Password saat ini wajib diisi untuk mengganti email.');
                        } elseif (! Hash::check($password, $this->user()->password)) {
                            $fail('Password saat ini tidak sesuai.');
                        }
                    }
                },
            ],
        ];
    }
}
