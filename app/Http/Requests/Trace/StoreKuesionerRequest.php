<?php

namespace App\Http\Requests\Trace;

use Illuminate\Foundation\Http\FormRequest;

class StoreKuesionerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Auth handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'judul'        => 'required|string|max:255',
            'subtitle'     => 'nullable|string',
            'kategori'     => 'nullable|string|in:Alumni,Stakeholder',
            'tahun'        => 'nullable|integer|min:2000|max:' . (date('Y') + 1),
            'date_mulai'   => 'nullable|date',
            'date_selesai' => 'nullable|date|after_or_equal:date_mulai',
            'deskripsi'    => 'nullable|string|max:65535',
            'status'       => 'required|string|in:draft,active,published,closed',
            'sections'     => 'nullable|array',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'judul.required'              => 'Judul kuesioner wajib diisi.',
            'judul.max'                   => 'Judul kuesioner maksimal 255 karakter.',
            'kategori.in'                 => 'Kategori yang dipilih tidak valid.',
            'tahun.integer'               => 'Tahun harus berupa angka.',
            'tahun.min'                   => 'Tahun minimal 2000.',
            'tahun.max'                   => 'Tahun tidak boleh lebih dari ' . (date('Y') + 1) . '.',
            'date_mulai.date'             => 'Format tanggal mulai tidak valid.',
            'date_selesai.date'           => 'Format tanggal selesai tidak valid.',
            'date_selesai.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai.',
            'deskripsi.max'               => 'Deskripsi terlalu panjang.',
            'status.required'             => 'Status kuesioner wajib dipilih.',
            'status.in'                   => 'Status yang dipilih tidak valid.',
        ];
    }
}
