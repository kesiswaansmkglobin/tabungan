<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'active' => $this->boolean('active'),
        ]);

        if ($this->has('criteria') && is_string($this->input('criteria'))) {
            $this->merge([
                'criteria' => json_decode($this->input('criteria'), true),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'xp_reward' => 'required|integer|min:0',
            'type' => 'required|string|max:50',
            'criteria' => 'nullable|array',
            'active' => 'boolean',
        ];
    }
}
