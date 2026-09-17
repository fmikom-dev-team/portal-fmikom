<?php

namespace App\Modules\Wims\Requests\Mahasiswa;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'registration_id.exists' => 'Periode pendaftaran yang dipilih tidak valid.',
            'registration_id' => ['nullable', 'integer', Rule::exists('pendaftaran_magangs', 'id')->where(fn ($query) => $query->where('mahasiswa_id', $this->user()?->id))],
            'proposal_pkl' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
            'tanggal_mulai' => ['required', 'date', 'after_or_equal:today'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'perusahaan_diminati_nama' => ['nullable', 'string', 'max:255'],
            'perusahaan_diminati_alamat' => ['nullable', 'string', 'max:1000'],
            'catatan_pengajuan' => ['nullable', 'string', 'max:1500'],
            'status_kip' => ['required', Rule::in(['kip', 'bukan_kip'])],
            'sks_ditempuh' => ['required', 'integer', 'min:0', 'max:300'],
            'transkrip_nilai' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
            'surat_rekomendasi_kaprodi' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
            'surat_rekomendasi_kaprodi_existing' => ['nullable', 'string'],
            'surat_rekomendasi_kaprodi_remove' => ['nullable', 'boolean'],
            'bidang_minat' => ['required', Rule::in([
                'Software Development',
                'Network & Infrastructure',
                'IT Business & Intelligence',
                'IT Multimedia',
                'Perekayasaan Perangkat Lunak',
                'Data Sains',
                'Arsitektur Enterprise',
                'Analyst',
                'Research Assistant',
                'Consultant',
                'Experts / Academics',
                'Lainnya',
            ])],
            'bidang_minat_lainnya' => ['nullable', 'required_if:bidang_minat,Lainnya', 'string', 'max:100'],
            'ukuran_seragam' => ['required', Rule::in(['S', 'M', 'L', 'XL', 'XXL', 'XXXL', 'Custom'])],
            'ukuran_seragam_custom' => ['nullable', 'required_if:ukuran_seragam,Custom', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
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
            'status_kip.required' => 'Status KIP wajib dipilih.',
            'status_kip.in' => 'Status KIP tidak valid.',
            'sks_ditempuh.required' => 'Jumlah SKS yang telah ditempuh wajib diisi.',
            'sks_ditempuh.integer' => 'Jumlah SKS harus berupa angka bulat.',
            'sks_ditempuh.min' => 'Jumlah SKS tidak boleh kurang dari 0.',
            'sks_ditempuh.max' => 'Jumlah SKS tidak boleh lebih dari 300.',
            'transkrip_nilai.file' => 'Transkrip nilai harus berupa file yang valid.',
            'transkrip_nilai.mimes' => 'Transkrip nilai harus berformat PDF.',
            'transkrip_nilai.max' => 'Ukuran transkrip nilai maksimal 5 MB.',
            'surat_rekomendasi_kaprodi.file' => 'Surat rekomendasi Kaprodi harus berupa file yang valid.',
            'surat_rekomendasi_kaprodi.mimes' => 'Surat rekomendasi Kaprodi harus berformat PDF.',
            'surat_rekomendasi_kaprodi.max' => 'Ukuran surat rekomendasi maksimal 5 MB.',
            'bidang_minat.required' => 'Bidang minat wajib dipilih.',
            'bidang_minat.in' => 'Bidang minat tidak valid.',
            'bidang_minat_lainnya.required_if' => 'Bidang minat lainnya wajib diisi.',
            'ukuran_seragam.required' => 'Ukuran seragam wajib dipilih.',
            'ukuran_seragam.in' => 'Ukuran seragam tidak valid.',
            'ukuran_seragam_custom.required_if' => 'Ukuran seragam custom wajib diisi.',
        ];
    }
}
