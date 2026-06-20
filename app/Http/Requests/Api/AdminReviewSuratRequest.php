<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminReviewSuratRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'decision' => ['required', Rule::in(['validated', 'rejected'])],
            'keperluan' => ['sometimes', 'string', 'min:10', 'max:1000'],
            'data' => ['sometimes', 'array'],
            'admin_note' => ['nullable', 'string', 'max:2000'],
            'rejection_reason' => ['required_if:decision,rejected', 'nullable', 'string', 'max:2000'],
        ];
    }
}
