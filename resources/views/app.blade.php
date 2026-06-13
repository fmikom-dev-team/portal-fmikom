<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    @class([
        'dark' => str_starts_with($page['component'] ?? '', 'Wims/Mahasiswa/')
            && ($appearance ?? 'system') == 'dark',
    ])
>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
            (function() {
                const componentName = @json($page['component'] ?? '');
                const isStudentPage = componentName.startsWith('Wims/Mahasiswa/');
                const appearanceKey = @json($appearanceCookieKey ?? 'appearance');

                if (!isStudentPage) {
                    document.documentElement.classList.remove('dark');
                    return;
                }

                function getCookie(name) {
                    const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
                    return match ? match[2] : null;
                }

                const cookieAppearance = getCookie(appearanceKey);
                const localAppearance = localStorage.getItem(appearanceKey);
                const appearance = cookieAppearance || localAppearance || '{{ $appearance ?? "system" }}';

                if (appearance === 'dark') {
                    document.documentElement.classList.add('dark');
                } else if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }
        </style>

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <link rel="icon" href="/logo.png" sizes="any">
        <link rel="apple-touch-icon" href="/logo.png">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        @vite(['resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
