<?php

namespace App\Modules\Pagi\Requests;

use App\Rules\VideoDurationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePortfolioRequest extends FormRequest
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
        $merges = [];
        if ($this->has('is_published')) {
            $merges['is_published'] = filter_var($this->is_published, FILTER_VALIDATE_BOOLEAN);
        }

        if ($this->has('content') && is_array($this->content)) {
            $content = $this->content;
            foreach ($content as $i => &$block) {
                if (is_array($block)) {
                    if (isset($block['file']) && ! ($block['file'] instanceof \Illuminate\Http\UploadedFile)) {
                        unset($block['file']);
                    }
                    if (isset($block['files']) && is_array($block['files'])) {
                        foreach ($block['files'] as $j => $f) {
                            if (! ($f instanceof \Illuminate\Http\UploadedFile)) {
                                unset($block['files'][$j]);
                            }
                        }
                    }
                }
            }
            $merges['content'] = $content;
        }

        if (! empty($merges)) {
            $this->merge($merges);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $isPublished = filter_var($this->is_published, FILTER_VALIDATE_BOOLEAN);

        $maxUploadMb = (int) (\App\Models\Portal\PortalSetting::query()->where('key', 'pagi_max_upload_size_mb')->value('value') ?? 10);
        $maxKb = max(1024, $maxUploadMb * 1024);

        $coverRule = [$isPublished ? 'required' : 'nullable'];
        if ($this->hasFile('cover_image') || $this->cover_image instanceof \Illuminate\Http\UploadedFile) {
            $coverRule = [$isPublished ? 'required' : 'nullable', 'file', 'extensions:jpeg,jpg,png,gif,webp,avif,heic,heif,svg,bmp,mp4,mov,qt,avi,webm,mkv,3gp', 'max:'.$maxKb, new VideoDurationRule];
        } elseif (is_string($this->cover_image) && ! empty($this->cover_image)) {
            $coverRule = [$isPublished ? 'required' : 'nullable', 'string'];
        }

        return [
            'title' => $isPublished ? 'required|string|max:255' : 'nullable|string|max:255',
            'content' => 'nullable|array',
            'content.*.type' => 'nullable|string',
            'content.*.file' => ['nullable', 'file', 'extensions:jpeg,jpg,png,gif,webp,avif,heic,heif,svg,bmp,mp4,mov,avi,webm,mkv,3gp,mp3,wav,ogg,pdf,zip,rar,tar,7z', 'max:'.$maxKb, new VideoDurationRule],
            'content.*.files.*' => ['nullable', 'file', 'extensions:jpeg,jpg,png,gif,webp,avif,heic,heif,svg,bmp,mp4,mov,avi,webm,mkv,3gp', 'max:'.$maxKb, new VideoDurationRule],
            'is_published' => 'boolean',
            'cover_image' => $coverRule,
            'category' => $isPublished ? 'required|string|max:100' : 'nullable|string|max:100',
            'tags' => $isPublished ? 'required|string' : 'nullable|string',
            'tools_used' => $isPublished ? 'required|string|max:255' : 'nullable|string|max:255',
            'description' => $isPublished ? 'required|string|max:2000' : 'nullable|string|max:2000',
            'visibility' => $isPublished ? 'required|string|in:Everyone,Private' : 'nullable|string|in:Everyone,Private',
            'collaborators' => 'nullable',
            'collaborators.*.id' => 'nullable|integer|exists:users,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'cover_image.required' => 'Foto/video sampul karya wajib diunggah sebelum dipublikasikan!',
            'cover_image.file' => 'Berkas sampul harus berupa file gambar atau video yang valid.',
            'cover_image.uploaded' => 'Berkas sampul gagal diunggah. Pastikan ukuran file tidak melebihi batas server (maksimal 20MB untuk video).',
            'cover_image.extensions' => 'Format berkas sampul tidak didukung (gunakan JPG, PNG, WEBP, atau MP4/MOV).',
            'cover_image.max' => 'Ukuran berkas sampul maksimal adalah 100MB.',
        ];
    }
}
