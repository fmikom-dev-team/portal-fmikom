<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Log;
use Illuminate\Translation\PotentiallyTranslatedString;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class VideoDurationRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value && method_exists($value, 'isValid') && $value->isValid()) {
            $mime = $value->getMimeType();
            if (str_starts_with($mime, 'video/')) {
                // Max 20MB limit
                if ($value->getSize() > 20 * 1024 * 1024) {
                    $fail('Ukuran video maksimal adalah 20MB.');

                    return;
                }
                // Duration limit 60 seconds
                $path = $value->getRealPath();
                try {
                    $duration = FFMpeg::fromDisk('local_root')
                        ->open(ltrim($path, '/'))
                        ->getDurationInSeconds();
                    if ($duration > 60.5) {
                        $fail('Durasi video maksimal adalah 1 menit (60 detik).');
                    }
                } catch (\Throwable $e) {
                    Log::error('FFprobe duration check failed: '.$e->getMessage());
                }
            }
        }
    }
}
