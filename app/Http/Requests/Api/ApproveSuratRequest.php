<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApproveSuratRequest extends FormRequest
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
            'decision' => ['required', Rule::in(['approved', 'revision_requested', 'rejected_final'])],
            'notes' => ['nullable', 'string', 'max:2000'],
            'rejection_reason' => ['required_if:decision,revision_requested,rejected_final', 'nullable', 'string', 'max:2000'],
        ];
    }
}
