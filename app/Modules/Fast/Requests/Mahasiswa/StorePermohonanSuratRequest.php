<?php

namespace App\Modules\Fast\Requests\Mahasiswa;

use Illuminate\Foundation\Http\FormRequest;

class StorePermohonanSuratRequest extends FormRequest
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
            'jenisSurat' => ['required', 'string', 'max:255'],
            'keperluan' => ['required', 'string', 'min:5', 'max:255'],
            'nim' => ['required', 'digits_between:8,20'],
            'tanggalKebutuhan' => ['required', 'date', 'after_or_equal:today'],
            'catatan' => ['nullable', 'string', 'max:1000'],
            'lampiran' => [
                'nullable',
                'file',
                'max:2048',
                'mimetypes:application/pdf,image/jpeg',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'jenisSurat.required' => 'Pilih jenis surat terlebih dahulu.',
            'keperluan.required' => 'Keperluan wajib diisi.',
            'keperluan.min' => 'Keperluan minimal 5 karakter.',
            'nim.required' => 'NIM wajib diisi.',
            'nim.digits_between' => 'NIM harus berupa angka 8-20 digit.',
            'tanggalKebutuhan.required' => 'Tanggal kebutuhan wajib diisi.',
            'tanggalKebutuhan.after_or_equal' => 'Tanggal kebutuhan tidak boleh di masa lalu.',
            'lampiran.file' => 'Lampiran harus berupa file yang valid.',
            'lampiran.max' => 'Ukuran lampiran maksimal 2 MB.',
            'lampiran.mimetypes' => 'Lampiran harus berformat PDF, JPG, atau JPEG.',
        ];
    }
}
