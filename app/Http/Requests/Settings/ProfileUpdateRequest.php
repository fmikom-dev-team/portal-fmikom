<?php

namespace App\Http\Requests\Settings;

use App\Concerns\ProfileValidationRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:255'],
            'bio'           => ['nullable', 'string', 'max:1000'],
            'location'      => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['nullable', 'date'],
            'no_telepon'    => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s]+$/'],
            'program_studi_id' => ['nullable', 'integer', 'exists:program_studis,id'],
            'tahun_lulus'   => ['nullable', 'integer', 'min:1990', 'max:' . (date('Y') + 5)],
            'website'    => ['nullable', 'string', 'max:255'],
            'twitter'    => ['nullable', 'string', 'max:100'],
            'linkedin'   => ['nullable', 'string', 'max:100'],
            'github'     => ['nullable', 'string', 'max:100'],
            // Validasi avatar_url: hanya domain tepercaya yang diizinkan (cegah SSRF)
            'avatar_url' => [
                'nullable',
                'url',
                'max:500',
                function ($attribute, $value, $fail) {
                    if (empty($value)) {
                        return;
                    }
                    $allowedDomains = [
                        'api.dicebear.com',
                        'avatars.dicebear.com',
                        'lh3.googleusercontent.com',
                        'lh4.googleusercontent.com',
                        'lh5.googleusercontent.com',
                        'lh6.googleusercontent.com',
                    ];
                    $host = parse_url($value, PHP_URL_HOST);
                    if (!$host || !in_array(strtolower($host), $allowedDomains)) {
                        $fail("URL {$attribute} tidak valid. Gunakan avatar dari DiceBear atau Google.");
                    }
                },
            ],
            'foto'       => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ];
    }
}
