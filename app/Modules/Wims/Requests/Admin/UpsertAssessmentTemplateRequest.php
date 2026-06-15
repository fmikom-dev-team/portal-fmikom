<?php

namespace App\Modules\Wims\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
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
            'assessor_role' => ['required', Rule::in(['dosen', 'mitra'])],
            'name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'year' => ['nullable', 'integer', 'digits:4', "between:{$minYear},{$maxYear}"],
            'periode_mulai' => ['required_without:year', 'date'],
            'periode_selesai' => ['required_without:year', 'date'],
            'is_active' => ['required', 'boolean'],
            'components' => ['required', 'array', 'min:1'],
            'components.*.id' => ['nullable', 'integer'],
            'components.*.name' => ['required', 'string', 'max:255'],
            'components.*.description' => ['nullable', 'string', 'max:1000'],
            'components.*.weight_percentage' => ['required', 'numeric', 'gte:0', 'lte:100'],
            'components.*.sort_order' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'assessor_role.required' => 'Role penilai wajib dipilih.',
            'assessor_role.in' => 'Role penilai hanya boleh dosen atau mitra.',
            'year.digits' => 'Tahun penilaian harus terdiri dari 4 digit.',
            'year.between' => 'Tahun penilaian harus berada pada rentang yang diizinkan.',
            'periode_mulai.required_without' => 'Periode mulai wajib diisi jika tahun penilaian tidak dipakai.',
            'periode_selesai.required_without' => 'Periode selesai wajib diisi jika tahun penilaian tidak dipakai.',
            'components.required' => 'Komponen penilaian wajib diisi.',
            'components.min' => 'Tambahkan minimal satu komponen penilaian.',
            'components.*.name.required' => 'Nama komponen wajib diisi.',
            'components.*.weight_percentage.required' => 'Bobot komponen wajib diisi.',
            'components.*.weight_percentage.gte' => 'Bobot komponen tidak boleh negatif.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $components = collect($this->input('components', []));

            if ($components->isEmpty()) {
                return;
            }

            $periodeMulai = $this->input('periode_mulai');
            $periodeSelesai = $this->input('periode_selesai');

            if ($periodeMulai && $periodeSelesai && strtotime((string) $periodeSelesai) < strtotime((string) $periodeMulai)) {
                $validator->errors()->add(
                    'periode_selesai',
                    'Periode selesai harus sama atau setelah periode mulai.',
                );
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

            $componentIds = $components
                ->pluck('id')
                ->filter(fn ($value) => filled($value))
                ->map(fn ($value) => (int) $value)
                ->values();

            if ($componentIds->count() !== $componentIds->unique()->count()) {
                $validator->errors()->add(
                    'components',
                    'Komponen template tidak boleh menggunakan ID yang sama lebih dari satu kali.',
                );
            }
        });
    }
}
