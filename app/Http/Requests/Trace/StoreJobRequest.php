<?php

namespace App\Http\Requests\Trace;

use Illuminate\Foundation\Http\FormRequest;

class StoreJobRequest extends FormRequest
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
            'title'            => 'required|string|max:255',
            'description'      => 'required|string|max:65535',
            'job_category_id'  => 'nullable|exists:job_categories,id',
            'experience_level' => 'required|in:fresh_graduate,junior,mid_level,senior,internship',
            'location_type'    => 'required|in:onsite,remote,hybrid',
            'location_city'    => 'nullable|string',
            'tipe_kerja'       => 'required|in:full_time,part_time,magang,freelance',
            'salary_min'       => 'nullable|integer|min:0',
            'salary_max'       => 'nullable|integer|min:0|gte:salary_min',
            'deadline'         => 'nullable|date|after:today',
            'is_salary_visible' => 'boolean',
            'status'           => 'in:draft,published,pending_review,closed',
            'mitra_id'         => 'nullable|exists:mitra_profiles,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required'            => 'Judul lowongan wajib diisi.',
            'title.max'                 => 'Judul lowongan maksimal 255 karakter.',
            'description.required'      => 'Deskripsi lowongan wajib diisi.',
            'description.max'           => 'Deskripsi terlalu panjang.',
            'job_category_id.exists'    => 'Kategori yang dipilih tidak valid.',
            'experience_level.required' => 'Level pengalaman wajib dipilih.',
            'experience_level.in'       => 'Level pengalaman yang dipilih tidak valid.',
            'location_type.required'    => 'Tipe lokasi wajib dipilih.',
            'location_type.in'          => 'Tipe lokasi yang dipilih tidak valid.',
            'tipe_kerja.required'       => 'Tipe pekerjaan wajib dipilih.',
            'tipe_kerja.in'             => 'Tipe pekerjaan yang dipilih tidak valid.',
            'salary_min.integer'        => 'Gaji minimum harus berupa angka.',
            'salary_min.min'            => 'Gaji minimum tidak boleh negatif.',
            'salary_max.integer'        => 'Gaji maksimum harus berupa angka.',
            'salary_max.min'            => 'Gaji maksimum tidak boleh negatif.',
            'salary_max.gte'            => 'Gaji maksimum harus lebih besar atau sama dengan gaji minimum.',
            'deadline.date'             => 'Format deadline tidak valid.',
            'deadline.after'            => 'Deadline harus setelah hari ini.',
            'status.in'                 => 'Status yang dipilih tidak valid.',
            'mitra_id.exists'           => 'Mitra yang dipilih tidak ditemukan.',
        ];
    }
}
