<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Custom FFmpeg settings for PAGI Video Conversion
    |--------------------------------------------------------------------------
    */

    // Delete the original uploaded video after converting it to WebM to optimize disk space
    'delete_original_after_conversion' => env('FFMPEG_DELETE_ORIGINAL', true),

    // Thread count for FFmpeg conversion processes (lower limits prevent CPU throttling on small servers)
    'conversion_threads' => env('FFMPEG_CONVERSION_THREADS', 2),
];
