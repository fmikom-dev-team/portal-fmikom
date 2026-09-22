<?php

return [
    /*
    |--------------------------------------------------------------------------
    | FAST PDF renderer
    |--------------------------------------------------------------------------
    |
    | Optional paths for deployments whose Chrome/Chromium or Times New Roman
    | fonts are installed outside the standard Windows/Linux locations.
    | Multiple font directories use the OS separator: `;` on Windows, `:` on
    | Linux. No setting is required for mPDF's built-in serif fallback.
    |
    */
    'pdf_chrome_path' => env('FAST_PDF_CHROME_PATH'),

    'pdf_font_directories' => env('FAST_PDF_FONT_DIRECTORIES', ''),

    'pdf_times_new_roman' => [
        'R' => env('FAST_PDF_TIMES_REGULAR', 'times.ttf'),
        'B' => env('FAST_PDF_TIMES_BOLD', 'timesbd.ttf'),
        'I' => env('FAST_PDF_TIMES_ITALIC', 'timesi.ttf'),
        'BI' => env('FAST_PDF_TIMES_BOLD_ITALIC', 'timesbi.ttf'),
    ],
];
