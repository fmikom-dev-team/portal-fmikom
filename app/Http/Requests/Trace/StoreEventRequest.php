<?php

namespace App\Http\Requests\Trace;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Auth handled by middleware
    }

    /**
     * Prepare the data for validation.
     *
     * Sanitize empty strings to null for time/optional fields.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'event_time_start' => $this->input('event_time_start') ?: null,
            'event_time_end' => $this->input('event_time_end') ?: null,
            'registration_deadline' => $this->input('registration_deadline') ?: null,
            'max_participants' => $this->input('max_participants') ?: null,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:65535',
            'location' => 'nullable|string|max:255',
            'event_date' => 'required|date',
            'event_time_start' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'event_time_end' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'registration_deadline' => 'nullable|date|before_or_equal:event_date',
            'max_participants' => 'nullable|integer|min:1',
            'status' => 'required|in:draft,published,closed',
            'poster' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Judul event wajib diisi.',
            'title.max' => 'Judul event maksimal 255 karakter.',
            'description.required' => 'Deskripsi event wajib diisi.',
            'description.max' => 'Deskripsi terlalu panjang.',
            'location.max' => 'Lokasi maksimal 255 karakter.',
            'event_date.required' => 'Tanggal event wajib diisi.',
            'event_date.date' => 'Format tanggal event tidak valid.',
            'event_time_start.regex' => 'Format waktu mulai tidak valid (HH:MM).',
            'event_time_end.regex' => 'Format waktu selesai tidak valid (HH:MM).',
            'registration_deadline.date' => 'Format deadline registrasi tidak valid.',
            'registration_deadline.before_or_equal' => 'Deadline registrasi harus sebelum atau sama dengan tanggal event.',
            'max_participants.integer' => 'Jumlah peserta maksimal harus berupa angka.',
            'max_participants.min' => 'Jumlah peserta maksimal minimal 1.',
            'status.required' => 'Status event wajib dipilih.',
            'status.in' => 'Status yang dipilih tidak valid.',
            'poster.image' => 'Poster harus berupa gambar.',
            'poster.mimes' => 'Poster harus berformat JPG, JPEG, PNG, atau WebP.',
            'poster.max' => 'Ukuran poster maksimal 2MB.',
        ];
    }
}
