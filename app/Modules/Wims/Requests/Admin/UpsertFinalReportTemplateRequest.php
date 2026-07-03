<?php

namespace App\Modules\Wims\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertFinalReportTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = in_array($this->method(), ['PUT', 'PATCH'], true);

        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'file' => [
                Rule::requiredIf(! $isUpdate),
                'file',
                'mimes:pdf,doc,docx',
                'max:10240',
            ],
            'is_active' => ['required', 'boolean'],
        ];
    }
}

