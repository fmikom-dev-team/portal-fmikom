<?php

namespace App\Http\Requests\Trace;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAlumniProfileRequest extends FormRequest
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
            // User table fields
            'name' => ['required', 'string', 'max:255'],
            'nomor_induk' => ['required', 'string', 'max:50'],
            'tahun_lulus' => ['nullable', 'integer', 'min:1900', 'max:'.(date('Y') + 5)],
            'no_telepon' => ['nullable', 'string', 'max:25'],
            'program_studi_id' => ['nullable', 'exists:program_studis,id'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'location' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'github' => ['nullable', 'string', 'max:255'],
            'instagram' => ['nullable', 'string', 'max:255'],
            'twitter' => ['nullable', 'string', 'max:255'],
            'linkedin' => ['nullable', 'string', 'max:255'],

            // ProfilAlumni table fields
            'jenis_kelamin' => ['nullable', 'in:L,P'],
            'angkatan' => ['required', 'integer', 'min:1900', 'max:'.(date('Y') + 5)],
            'nik' => ['nullable', 'string', 'size:16'],
            'npwp' => ['nullable', 'string', 'max:30'],
            'provinsi_id' => ['nullable', 'exists:provinsi,id'],
            'kota_id' => ['nullable', 'exists:kota,id'],
            'alamat_rumah' => ['nullable', 'string'],
            'latitude_rumah' => ['nullable', 'numeric'],
            'longitude_rumah' => ['nullable', 'numeric'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'name.max' => 'Nama maksimal 255 karakter.',
            'nomor_induk.required' => 'Nomor induk wajib diisi.',
            'nomor_induk.max' => 'Nomor induk maksimal 50 karakter.',
            'tahun_lulus.integer' => 'Tahun lulus harus berupa angka.',
            'tahun_lulus.min' => 'Tahun lulus tidak valid.',
            'tahun_lulus.max' => 'Tahun lulus tidak valid.',
            'no_telepon.max' => 'Nomor telepon maksimal 25 karakter.',
            'program_studi_id.exists' => 'Program studi yang dipilih tidak valid.',
            'bio.max' => 'Bio maksimal 1000 karakter.',
            'website.url' => 'Format URL website tidak valid.',
            'jenis_kelamin.in' => 'Jenis kelamin harus L atau P.',
            'angkatan.required' => 'Tahun angkatan wajib diisi.',
            'angkatan.integer' => 'Tahun angkatan harus berupa angka.',
            'angkatan.min' => 'Tahun angkatan tidak valid.',
            'angkatan.max' => 'Tahun angkatan tidak valid.',
            'nik.size' => 'NIK harus 16 digit.',
            'npwp.max' => 'NPWP maksimal 30 karakter.',
            'provinsi_id.exists' => 'Provinsi yang dipilih tidak valid.',
            'kota_id.exists' => 'Kota yang dipilih tidak valid.',
            'latitude_rumah.numeric' => 'Latitude harus berupa angka.',
            'longitude_rumah.numeric' => 'Longitude harus berupa angka.',
        ];
    }
}
