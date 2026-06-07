<?php

return [
    'ffmpeg' => [
        'binaries' => env('FFMPEG_BINARIES', 'ffmpeg'),

        'threads' => env('FFMPEG_THREADS', 4),
    ],

    'ffprobe' => [
        'binaries' => env('FFPROBE_BINARIES', 'ffprobe'),
    ],

    'timeout' => env('FFMPEG_TIMEOUT', 3600),

    'enable_logging' => env('FFMPEG_LOGGING', true),

    'set_command_and_ffmpeg_binary_on_from_disk' => false,
];
