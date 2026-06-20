<?php

namespace App\Modules\Pagi\Requests;

use App\Rules\VideoDurationRule;
use Illuminate\Foundation\Http\FormRequest;

class QuickStorePortfolioRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'cover_image' => ['required', 'file', 'extensions:jpeg,jpg,png,gif,webp,mp4,mov,avi,webm,mkv,3gp', 'max:102400', new VideoDurationRule()],
            'skills' => ['required', 'string', function ($attribute, $value, $fail) {
                $array = json_decode($value, true);
                if (! is_array($array) || count($array) < 1) {
                    $fail('Pilih minimal 1 keahlian (skills).');
                }
            }],
            'tools' => ['required', 'string', function ($attribute, $value, $fail) {
                $array = json_decode($value, true);
                if (! is_array($array) || count($array) < 1) {
                    $fail('Pilih minimal 1 tools.');
                }
            }],
            'completed_work_link' => 'nullable|url|max:255',
            'collaborators' => 'nullable',
            'client' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'industry' => 'nullable|string|max:255',
            'original_work_confirmed' => 'nullable|string',
            'cover_fit' => 'nullable|string|in:cover,contain',
        ];
    }
}
