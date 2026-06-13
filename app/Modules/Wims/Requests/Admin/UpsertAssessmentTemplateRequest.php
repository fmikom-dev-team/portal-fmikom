<?php

namespace App\Modules\Wims\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpsertAssessmentTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $currentYear = (int) now()->format('Y');
        $minYear = $currentYear - 2;
        $maxYear = $currentYear + 9;

        return [
            'name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'year' => ['required', 'integer', 'digits:4', "between:{$minYear},{$maxYear}"],
            'is_active' => ['required', 'boolean'],
            'components' => ['required', 'array', 'min:1'],
            'components.*.id' => ['nullable', 'integer'],
            'components.*.name' => ['required', 'string', 'max:255'],
            'components.*.description' => ['nullable', 'string', 'max:1000'],
            'components.*.weight_percentage' => ['required', 'numeric', 'gt:0', 'lte:100'],
            'components.*.sort_order' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'year.required' => 'Tahun penilaian wajib dipilih.',
            'year.digits' => 'Tahun penilaian harus terdiri dari 4 digit.',
            'year.between' => 'Tahun penilaian harus berada pada rentang yang diizinkan.',
            'components.required' => 'Komponen penilaian wajib diisi.',
            'components.min' => 'Tambahkan minimal satu komponen penilaian.',
            'components.*.name.required' => 'Nama komponen wajib diisi.',
            'components.*.weight_percentage.required' => 'Bobot komponen wajib diisi.',
            'components.*.weight_percentage.gt' => 'Bobot komponen harus lebih dari 0.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $components = collect($this->input('components', []));

            if ($components->isEmpty()) {
                return;
            }

            $totalWeight = round(
                $components->sum(fn ($component) => (float) data_get($component, 'weight_percentage', 0)),
                2,
            );

            if (abs($totalWeight - 100.0) > 0.01) {
                $validator->errors()->add(
                    'components',
                    'Total bobot seluruh komponen harus tepat 100%.',
                );
            }
        });
    }
}
