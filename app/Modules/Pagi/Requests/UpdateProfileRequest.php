<?php

namespace App\Modules\Pagi\Requests;

use App\Rules\VideoDurationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'role_title' => 'nullable|string|max:100',
            'banner' => [
                'nullable',
                'file',
                'mimes:jpeg,png,jpg,gif,webp,mp4,webm,ogg',
                'max:102400',
                new VideoDurationRule(),
            ],
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'bio' => 'nullable|string|max:1000',
            'location' => 'nullable|string|max:100',
            'website' => 'nullable|string|max:255',
            'twitter' => 'nullable|string|max:100',
            'linkedin' => 'nullable|string|max:100',
            'github' => 'nullable|string|max:100',
            'instagram' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'is_message_enabled' => 'nullable|boolean',
            'skills' => 'nullable|array',
            'timezone' => 'nullable|string|max:100',
            'timezone_extended' => 'nullable|string|max:100',
            'languages' => 'nullable|array',
            'avatar_url' => 'nullable|string|max:2048',
            'remove_foto' => 'nullable|boolean',
            'pagi_username' => [
                'nullable',
                'string',
                'min:3',
                'max:30',
                'regex:/^[a-z0-9._]+$/',
                Rule::unique('users', 'pagi_username')->ignore(Auth::id()),
            ],
        ];
    }
}
