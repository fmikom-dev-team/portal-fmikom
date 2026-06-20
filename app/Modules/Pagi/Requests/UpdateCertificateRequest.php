<?php

namespace App\Modules\Pagi\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCertificateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'issuer' => 'required|string|max:255',
            'date' => 'required|string|max:100',
            'expirationDate' => 'nullable|string|max:100',
            'credentialId' => 'nullable|string|max:255',
            'credentialUrl' => 'nullable|url|max:2083',
            'skills' => 'nullable|string',
            'existingMedia' => 'nullable|string',
            'newMedia' => 'nullable|array|max:3',
            'newMedia.*' => 'file|mimes:jpeg,png,jpg,gif,webp,pdf|max:20480',
        ];
    }
}
