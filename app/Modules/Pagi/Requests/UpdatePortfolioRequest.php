<?php

namespace App\Modules\Pagi\Requests;

use App\Rules\VideoDurationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePortfolioRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('is_published')) {
            $this->merge([
                'is_published' => filter_var($this->is_published, FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $isPublished = filter_var($this->is_published, FILTER_VALIDATE_BOOLEAN);

        return [
            'title' => $isPublished ? 'required|string|max:255' : 'nullable|string|max:255',
            'content' => 'nullable|array',
            'content.*.type' => 'nullable|string',
            'content.*.file' => ['nullable', 'file', 'extensions:jpeg,jpg,png,gif,webp,mp4,mov,avi,webm,mkv,3gp,mp3,wav,ogg,pdf,zip,rar,tar,7z', 'max:102400', new VideoDurationRule()],
            'content.*.files.*' => ['nullable', 'file', 'extensions:jpeg,jpg,png,gif,webp,mp4,mov,avi,webm,mkv,3gp', 'max:102400', new VideoDurationRule()],
            'is_published' => 'boolean',
            'cover_image' => ['nullable', 'file', 'extensions:jpeg,jpg,png,gif,webp,mp4,mov,avi,webm,mkv,3gp', 'max:102400', new VideoDurationRule()],
            'category' => $isPublished ? 'required|string|max:100' : 'nullable|string|max:100',
            'tags' => $isPublished ? 'required|string' : 'nullable|string',
            'tools_used' => $isPublished ? 'required|string|max:255' : 'nullable|string|max:255',
            'description' => $isPublished ? 'required|string|max:2000' : 'nullable|string|max:2000',
            'visibility' => $isPublished ? 'required|string|in:Everyone,Private' : 'nullable|string|in:Everyone,Private',
            'collaborators' => 'nullable',
        ];
    }
}
