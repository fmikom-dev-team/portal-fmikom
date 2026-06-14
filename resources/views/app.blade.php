<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script nonce="{{ $csp_nonce ?? '' }}">
            (function() {
                const appearance = '{{ $appearance ?? "system" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style nonce="{{ $csp_nonce ?? '' }}">
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }
        </style>

        <title inertia>{{ $metaTitle ?? config('app.name', 'Laravel') }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <!-- SEO Meta Tags -->
        <meta name="description" content="{{ $metaDescription ?? 'FMIKOM Portal - Academic & Creative Network Fakultas Ilmu Komputer. Tempat berbagi karya, portofolio, dan kolaborasi mahasiswa dan creator.' }}">
        
        <!-- Open Graph / Facebook -->
        <meta property="og:type" content="{{ $metaType ?? 'profile' }}">
        <meta property="og:title" content="{{ $metaTitle ?? 'FMIKOM Portal — Academic & Creative Network' }}">
        <meta property="og:description" content="{{ $metaDescription ?? 'Tempat berbagi karya, portofolio, dan kolaborasi mahasiswa dan creator Fakultas Ilmu Komputer.' }}">
        <meta property="og:image" content="{{ $metaImage ?? asset('og-image.png') }}">

        <!-- Twitter -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $metaTitle ?? 'FMIKOM Portal — Academic & Creative Network' }}">
        <meta name="twitter:description" content="{{ $metaDescription ?? 'Tempat berbagi karya, portofolio, dan kolaborasi mahasiswa dan creator Fakultas Ilmu Komputer.' }}">
        <meta name="twitter:image" content="{{ $metaImage ?? asset('og-image.png') }}">

        <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
        <link rel="dns-prefetch" href="https://fonts.bunny.net">
        <link rel="preload" as="style" href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600|inter:300,400,500,600,700,800&display=swap">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600|inter:300,400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/js/app.ts'])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        <div id="app" data-page="{{ json_encode($page ?? []) }}"></div>
        @inertia(['nonce' => $csp_nonce ?? ''])
    </body>
</html>
