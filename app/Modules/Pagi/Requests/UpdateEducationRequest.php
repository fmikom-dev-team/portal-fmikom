<?php

namespace App\Modules\Pagi\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEducationRequest extends FormRequest
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
            'level' => 'required|string|max:100',
            'institution' => 'required|string|max:255',
            'major' => 'nullable|string|max:255',
            'start_date' => 'required|string|max:100',
            'end_date' => 'required|string|max:100',
            'description' => 'nullable|string|max:1000',
        ];
    }
}
