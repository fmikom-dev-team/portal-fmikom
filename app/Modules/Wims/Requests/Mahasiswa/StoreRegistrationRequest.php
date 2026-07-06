<?php

namespace App\Modules\Wims\Requests\Mahasiswa;

use Illuminate\Foundation\Http\FormRequest;

class StoreRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'proposal_pkl' => ['required_without:proposal_pkl_existing', 'nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
            'proposal_pkl_existing' => ['nullable', 'string'],
            'tanggal_mulai' => ['required', 'date', 'after_or_equal:today'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'perusahaan_diminati_nama' => ['nullable', 'string', 'max:255'],
            'perusahaan_diminati_alamat' => ['nullable', 'string', 'max:1000'],
            'catatan_pengajuan' => ['nullable', 'string', 'max:1500'],
        ];
    }

    public function messages(): array
    {
        return [
            'proposal_pkl.required_without' => 'Proposal PKL wajib dilampirkan saat pendaftaran.',
            'proposal_pkl.file' => 'Proposal PKL harus berupa file yang valid.',
            'proposal_pkl.mimes' => 'Proposal PKL harus berformat PDF, DOC, atau DOCX.',
            'proposal_pkl.max' => 'Ukuran proposal PKL maksimal 5 MB.',
            'tanggal_mulai.required' => 'Tanggal mulai PKL/magang wajib diisi.',
            'tanggal_mulai.after_or_equal' => 'Tanggal mulai tidak boleh sebelum hari ini.',
            'tanggal_selesai.required' => 'Tanggal selesai PKL/magang wajib diisi.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
            'perusahaan_diminati_nama.max' => 'Nama perusahaan diminati maksimal 255 karakter.',
            'perusahaan_diminati_alamat.max' => 'Alamat perusahaan diminati maksimal 1000 karakter.',
            'catatan_pengajuan.max' => 'Catatan pengajuan maksimal 1500 karakter.',
        ];
    }
}
