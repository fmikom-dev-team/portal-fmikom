<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script nonce="{{ $csp_nonce ?? '' }}">
            (function() {
                const path = window.location.pathname;
                const isPublic = path === '/' || path.indexOf('/login') === 0 || path.indexOf('/register') === 0 || path.indexOf('/signup') === 0 || path.indexOf('/two-factor') === 0;
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                let isDark = false;

                if (isPublic) {
                    isDark = prefersDark;
                } else {
                    const appearance = '{{ $appearance ?? "system" }}';
                    isDark = appearance === 'dark' || (appearance === 'system' && prefersDark);
                }

                if (isDark) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }

                // Dynamically update theme-color meta tag for seamless status bar (Image 2 style)
                const themeColor = isDark ? '#0f172a' : '#ffffff';
                let meta = document.querySelector('meta[name="theme-color"]');
                if (!meta) {
                    meta = document.createElement('meta');
                    meta.name = 'theme-color';
                    document.head.appendChild(meta);
                }
                meta.content = themeColor;
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

        @php
            $portalSettings = \Illuminate\Support\Facades\Cache::rememberForever('portal_settings', function () {
                $raw = \App\Models\Portal\PortalSetting::pluck('value', 'key')->toArray();
                $raw['brand_name'] = $raw['brand_name'] ?? 'Portal FMIKOM';
                $raw['brand_subtitle'] = $raw['brand_subtitle'] ?? 'Fakultas Matematika dan Ilmu Komputer';
                $raw['brand_logo'] = $raw['brand_logo'] ?? '/asset/brand-logo.webp';
                $raw['brand_favicon'] = $raw['brand_favicon'] ?? '/asset/brand-logo.webp';
                return $raw;
            });
            $brandFavicon = $portalSettings['brand_favicon'] ?? '/asset/brand-logo.webp';
        @endphp
        <link rel="icon" href="{{ $brandFavicon }}">
        <link rel="apple-touch-icon" href="{{ $portalSettings['brand_logo'] ?? '/asset/brand-logo.webp' }}">
        
        <!-- PWA Manifest & iOS Standalone Settings -->
        <link rel="manifest" href="/manifest.json">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta name="apple-mobile-web-app-title" content="Portal FMIKOM">
        <meta name="mobile-web-app-capable" content="yes">

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
        <link id="font-stylesheet" rel="preload" as="style" href="https://fonts.bunny.net/css?family=plus-jakarta-sans:300,400,500,600,700,800|instrument-sans:400,500,600|inter:300,400,500,600,700,800&display=swap" crossorigin>
        <script nonce="{{ $csp_nonce ?? '' }}">
            document.getElementById('font-stylesheet').rel = 'stylesheet';
        </script>
        <noscript>
            <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:300,400,500,600,700,800|instrument-sans:400,500,600|inter:300,400,500,600,700,800&display=swap" rel="stylesheet" />
        </noscript>

        @vite(['resources/js/app.ts'])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
