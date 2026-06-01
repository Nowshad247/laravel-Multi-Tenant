<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
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
        <style>
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }
        </style>

        @php
            $settings = $settings ?? $page['props']['settings'] ?? [];
            $siteName = $settings['site_name'] ?? config('app.name', 'Laravel');
            $siteDescription = $settings['site_description'] ?? '';
            $siteKeywords = $settings['site_keywords'] ?? '';
            $siteAuthor = $settings['site_author'] ?? '';
            $siteIcon = $settings['site_icon'] ?? null;
            $siteLogo = $settings['site_logo'] ?? null;
            $iconUrl = $siteIcon ? (\Illuminate\Support\Str::startsWith($siteIcon, ['http://', 'https://', '/']) ? $siteIcon : asset($siteIcon)) : asset('favicon.ico');
            $logoUrl = $siteLogo ? (\Illuminate\Support\Str::startsWith($siteLogo, ['http://', 'https://', '/']) ? $siteLogo : asset($siteLogo)) : asset('apple-touch-icon.png');
        @endphp

        <title inertia>{{ $siteName }}</title>
        <meta name="description" content="{{ $siteDescription }}">
        <meta name="keywords" content="{{ $siteKeywords }}">
        <meta name="author" content="{{ $siteAuthor }}">

        <link rel="icon" href="{{ $iconUrl }}" sizes="any">
        <link rel="icon" href="{{ $iconUrl }}" type="image/svg+xml">
        <link rel="apple-touch-icon" href="{{ $logoUrl }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        @viteReactRefresh
        @vite(['resources/js/app.tsx', "resources/js/pages/{$page['component']}.tsx"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
