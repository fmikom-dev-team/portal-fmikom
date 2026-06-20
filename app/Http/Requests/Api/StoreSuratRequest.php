<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreSuratRequest extends FormRequest
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
            'jenis_surat_id' => ['required', 'integer', 'exists:jenis_surats,id'],
            'type' => ['nullable', 'string', 'max:50'],
            'keperluan' => ['required', 'string', 'min:10', 'max:1000'],
            'tanggal_kebutuhan' => ['nullable', 'date'],
            'data' => ['nullable', 'array'],
            'lampiran' => ['nullable', 'array'],
            'lampiran.*' => ['file', 'max:4096', 'mimes:pdf,jpg,jpeg,png,doc,docx'],
        ];
    }
}
