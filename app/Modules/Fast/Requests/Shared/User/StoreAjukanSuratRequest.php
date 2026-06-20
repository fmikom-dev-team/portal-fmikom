<?php

namespace App\Modules\Fast\Requests\Shared\User;

use Illuminate\Foundation\Http\FormRequest;

class StoreAjukanSuratRequest extends FormRequest
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
            'keperluan' => ['required', 'string', 'min:10', 'max:1000'],
            'tanggalKebutuhan' => ['required', 'date', 'after_or_equal:today'],
            'lampiran' => [
                'nullable',
                'file',
                'max:2048',
                'mimes:pdf,jpg,jpeg,png',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'jenisSurat.required' => 'Jenis surat wajib dipilih.',
            'keperluan.required' => 'Keperluan wajib diisi.',
            'keperluan.min' => 'Keperluan minimal 10 karakter.',
            'tanggalKebutuhan.required' => 'Tanggal dibutuhkan wajib diisi.',
            'tanggalKebutuhan.after_or_equal' => 'Tanggal dibutuhkan tidak boleh di masa lalu.',
            'lampiran.file' => 'Lampiran harus berupa file yang valid.',
            'lampiran.max' => 'Ukuran lampiran maksimal 2 MB.',
            'lampiran.mimes' => 'Lampiran harus berformat PDF, JPG, JPEG, atau PNG.',
        ];
    }
}
