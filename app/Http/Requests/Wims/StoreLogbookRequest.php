<?php

namespace App\Http\Requests\Wims;

use Illuminate\Foundation\Http\FormRequest;

class StoreLogbookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'aktivitas_harian' => ['required', 'string', 'min:20'],
            'kompetensi_dicapai' => ['required', 'string', 'min:10'],
            'photos' => ['nullable', 'array', 'max:3'],
            'photos.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'jam_mulai.required' => 'Jam mulai wajib diisi.',
            'jam_mulai.date_format' => 'Format jam mulai tidak valid.',
            'jam_selesai.required' => 'Jam selesai wajib diisi.',
            'jam_selesai.date_format' => 'Format jam selesai tidak valid.',
            'jam_selesai.after' => 'Jam selesai harus setelah jam mulai.',
            'aktivitas_harian.required' => 'Aktivitas harian wajib diisi.',
            'aktivitas_harian.min' => 'Aktivitas harian minimal 20 karakter.',
            'kompetensi_dicapai.required' => 'Kompetensi yang dicapai wajib diisi.',
            'kompetensi_dicapai.min' => 'Kompetensi yang dicapai minimal 10 karakter.',
            'photos.array' => 'Lampiran foto tidak valid.',
            'photos.max' => 'Maksimal 3 foto.',
            'photos.*.image' => 'Setiap lampiran harus berupa gambar.',
            'photos.*.mimes' => 'Format foto harus JPG atau PNG.',
            'photos.*.max' => 'Ukuran setiap foto maksimal 2 MB.',
        ];
    }
}
