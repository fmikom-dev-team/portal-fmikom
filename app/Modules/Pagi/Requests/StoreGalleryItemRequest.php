<?php

namespace App\Modules\Pagi\Requests;

use App\Rules\VideoDurationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreGalleryItemRequest extends FormRequest
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
            'title' => 'nullable|string|max:255',
            'cover_image' => ['required', 'file', 'extensions:jpeg,jpg,png,gif,webp,mp4,mov,avi,webm,mkv,3gp', 'max:102400', new VideoDurationRule],
            'description' => 'nullable|string|max:2000',
        ];
    }
}
