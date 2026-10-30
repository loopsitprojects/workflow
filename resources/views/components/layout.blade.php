<!DOCTYPE html>
@php
    $activeTheme = \App\Services\FeatureManager::getActiveTheme();
    $isHalloween = \App\Services\FeatureManager::isHalloweenThemeEnabled();
    $isChristmas = \App\Services\FeatureManager::isChristmasThemeEnabled();
    $serverTheme = $isHalloween ? 'halloween' : ($isChristmas ? 'christmas' : ($activeTheme === 'dark' ? 'dark' : 'light'));
@endphp
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      class="{{ $isHalloween ? 'dark halloween' : ($isChristmas ? 'dark christmas' : ($activeTheme === 'dark' ? 'dark' : '')) }}"
      data-theme="{{ $serverTheme }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Loops' }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('desktop.png') }}">
    
    <!-- PWA Setup -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#ffffff">
    <link rel="apple-touch-icon" href="{{ asset('desktop-512.png') }}">
    
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js');
            });
        }
    </script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @php
        $defaultManifest = public_path('build/manifest.json');
        $altManifests = [
            $defaultManifest,
            base_path('public/build/manifest.json'),
            base_path('public_html/build/manifest.json'),
            base_path('public_html/public/build/manifest.json'),
            dirname(public_path()) . '/build/manifest.json',
        ];
        $foundManifest = null;
        foreach ($altManifests as $mPath) {
            if (file_exists($mPath)) {
                $foundManifest = $mPath;
                break;
            }
        }
    @endphp

    @if($foundManifest && $foundManifest === $defaultManifest)
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @elseif($foundManifest)
        @php
            $manifestData = json_decode(file_get_contents($foundManifest), true) ?: [];
            $cssEntry = $manifestData['resources/css/app.css']['file'] ?? null;
            $jsEntry  = $manifestData['resources/js/app.js']['file'] ?? null;
        @endphp
        @if($cssEntry)
            <link rel="stylesheet" href="{{ asset('build/' . $cssEntry) }}">
        @endif
        @if($jsEntry)
            <script type="module" src="{{ asset('build/' . $jsEntry) }}"></script>
        @endif
    @else
        @php
            $cssFile = null;
            $jsFile  = null;
            $assetDirs = [
                public_path('build/assets'),
                base_path('public_html/public/build/assets'),
                base_path('public_html/build/assets'),
            ];
            foreach ($assetDirs as $dir) {
                if (is_dir($dir)) {
                    foreach (scandir($dir) as $f) {
                        if (str_ends_with($f, '.css') && str_starts_with($f, 'app-')) $cssFile = $f;
                        if (str_ends_with($f, '.js') && str_starts_with($f, 'app-')) $jsFile = $f;
                    }
                    if ($cssFile || $jsFile) break;
                }
            }
        @endphp
        @if($cssFile)
            <link rel="stylesheet" href="{{ asset('build/assets/' . $cssFile) }}">
        @endif
        @if($jsFile)
            <script type="module" src="{{ asset('build/assets/' . $jsFile) }}"></script>
        @endif
    @endif
    
    <!-- Quill.js for rich text editing -->
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>

    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Inter', sans-serif; background-color: var(--color-bg-secondary, #F1F5F9); color: var(--color-text-primary, #0F172A); transition: background-color 0.3s, color 0.3s; }
        .dark body, body.dark { background-color: var(--color-bg-secondary, #0b1120); }

        /* Premium dark background — subtle blue-tinted radial gradient */
        .dark main, html.dark body { background-color: #0b1120; }
        html:not(.dark) body { background-color: #F1F5F9; }

        .glass { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.3); }
        .dark .glass { background: rgba(17, 24, 39, 0.75); border-color: rgba(255, 255, 255, 0.06); }
        .nav-active { color: var(--color-text-primary); font-weight: 600; border-bottom: 2px solid var(--color-text-primary); }

        /* Light mode: crisp, refined shadow with subtle border. Dark mode: visible depth without harsh outline */
        .card-shadow {
            background-color: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05), 0 1px 2px rgba(15, 23, 42, 0.03);
        }
        .dark .card-shadow {
            background-color: var(--color-bg-primary, #1e293b);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 1px 0 rgba(255,255,255,0.04) inset, 0 4px 24px rgba(0, 0, 0, 0.35);
        }

        /* =========================================================
           MODERN LUXURY SEASONAL DESIGN SYSTEM (Linear / Vercel style)
           ========================================================= */

        /* --- 1. DARK MODE ("Obsidian Midnight") --- */
        :root[data-theme="festive-dark-premium"],
        html.dark.halloween {
            --bg-main: #0B0A0F;
            --bg-card: rgba(22, 19, 31, 0.75);
            --border-card: rgba(255, 255, 255, 0.08);
            --text-heading: #F4F0F8;
            --text-body: #A198AC;
            --text-muted: #6B6275;
            --accent-primary: #F97316; /* Amber Glow */
            --accent-secondary: #8B5CF6; /* Royal Violet / Deep Amethyst */
            --shadow-card: 0 8px 32px rgba(0, 0, 0, 0.5);

            --color-bg-primary: rgba(22, 19, 31, 0.75);
            --color-bg-secondary: #0B0A0F;
            --color-text-primary: #F4F0F8;
            --color-text-secondary: #A198AC;
            --color-border-primary: rgba(255, 255, 255, 0.08);
        }

        html.dark.halloween body {
            background-color: #0B0A0F !important;
            background-image: 
                radial-gradient(circle at 50% 0%, rgba(249, 115, 22, 0.06) 0%, transparent 60%),
                radial-gradient(circle at 85% 15%, rgba(139, 92, 246, 0.08) 0%, transparent 45%),
                radial-gradient(circle at 15% 85%, rgba(249, 115, 22, 0.04) 0%, transparent 45%) !important;
            background-attachment: fixed !important;
            color: #F4F0F8 !important;
        }
        html.dark.halloween main {
            background-color: transparent !important;
        }

        /* Semi-translucent Glassmorphic Dark Card Surface & Precise 1px Border */
        html.dark.halloween .card-shadow,
        .dark.halloween .card-shadow {
            background-color: rgba(22, 19, 31, 0.75) !important;
            backdrop-filter: blur(16px) !important;
            -webkit-backdrop-filter: blur(16px) !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(255, 255, 255, 0.05) !important;
        }
        html.dark.halloween .card-shadow:hover,
        .dark.halloween .card-shadow:hover {
            border-color: rgba(249, 115, 22, 0.35) !important;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.6), 0 0 20px rgba(249, 115, 22, 0.15), inset 0 1px 0 rgba(255, 255, 255, 0.08) !important;
        }
        html.dark.halloween [class*="dark:bg-[#111827]"],
        html.dark.halloween [class*="dark:bg-[#0F172A]"],
        .dark.halloween [class*="dark:bg-[#111827]"],
        .dark.halloween [class*="dark:bg-[#0F172A]"] {
            background-color: rgba(22, 19, 31, 0.75) !important;
            backdrop-filter: blur(16px) !important;
            -webkit-backdrop-filter: blur(16px) !important;
        }
        html.dark.halloween [class*="dark:border-slate-800"],
        html.dark.halloween [class*="dark:border-white/[0.05]"],
        html.dark.halloween [class*="dark:border-white/[0.06]"],
        .dark.halloween [class*="dark:border-slate-800"],
        .dark.halloween [class*="dark:border-white/[0.05]"],
        .dark.halloween [class*="dark:border-white/[0.06]"] {
            border-color: rgba(255, 255, 255, 0.08) !important;
        }
        html.dark.halloween .glass,
        .dark.halloween .glass {
            background: rgba(22, 19, 31, 0.8) !important;
            backdrop-filter: blur(20px) !important;
            -webkit-backdrop-filter: blur(20px) !important;
            border-color: rgba(255, 255, 255, 0.08) !important;
        }

        /* Dark Mode High-Contrast Typography */
        html.dark.halloween h1,
        html.dark.halloween h2,
        html.dark.halloween h3,
        html.dark.halloween h4,
        .dark.halloween h1,
        .dark.halloween h2,
        .dark.halloween h3,
        .dark.halloween h4 {
            color: #F4F0F8 !important;
        }
        html.dark.halloween .text-gray-400,
        html.dark.halloween .text-slate-500,
        html.dark.halloween .text-gray-500,
        .dark.halloween .text-gray-400,
        .dark.halloween .text-slate-500,
        .dark.halloween .text-gray-500 {
            color: #A198AC !important;
        }
        html.dark.halloween .text-gray-700,
        html.dark.halloween .text-slate-300,
        .dark.halloween .text-gray-700,
        .dark.halloween .text-slate-300 {
            color: #E2DDF0 !important;
        }

        /* Dark Mode Luxury Accents (Warm Burnished Gold-Orange & Subtle Royal Violet) */
        html.dark.halloween .nav-active,
        .dark.halloween .nav-active {
            color: #F97316 !important;
            background: rgba(249, 115, 22, 0.12) !important;
            border: 1px solid rgba(249, 115, 22, 0.25) !important;
            border-radius: 9999px !important;
            border-bottom: 1px solid rgba(249, 115, 22, 0.25) !important;
            backdrop-filter: blur(8px) !important;
            box-shadow: 0 0 12px rgba(249, 115, 22, 0.15) !important;
        }
        html.dark.halloween .bg-blue-600,
        html.dark.halloween .bg-blue-500,
        .dark.halloween .bg-blue-600,
        .dark.halloween .bg-blue-500 {
            background-color: #F97316 !important;
            color: #0B0A0F !important;
        }
        html.dark.halloween .text-blue-600,
        html.dark.halloween .text-blue-500,
        html.dark.halloween .dark\:text-blue-400,
        .dark.halloween .text-blue-600,
        .dark.halloween .text-blue-500,
        .dark.halloween .dark\:text-blue-400 {
            color: #F97316 !important;
        }
        html.dark.halloween .bg-blue-50,
        html.dark.halloween .dark\:bg-blue-500\/10,
        .dark.halloween .bg-blue-50,
        .dark.halloween .dark\:bg-blue-500\/10 {
            background-color: rgba(249, 115, 22, 0.12) !important;
            color: #F97316 !important;
        }

        /* Card watermark: Spooky Jack-o'-Lantern (Dark Mode) */
        html.dark.halloween .card-shadow::after {
            content: '';
            position: absolute;
            bottom: -4px;
            right: 8px;
            width: 60px;
            height: 60px;
            pointer-events: none;
            opacity: 0.12;
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Cpath fill='%23F97316' d='M50,15 C45,5 35,5 32,8 C38,14 42,15 45,18 C30,16 10,28 10,55 C10,80 30,92 50,92 C70,92 90,80 90,55 C90,28 70,16 55,18 C58,15 62,14 68,8 C65,5 55,5 50,15 Z'/%3E%3Cpolygon points='32,45 42,48 36,36' fill='%230B0A0F'/%3E%3Cpolygon points='68,45 58,48 64,36' fill='%230B0A0F'/%3E%3Cpolygon points='50,50 45,58 55,58' fill='%230B0A0F'/%3E%3Cpath d='M28,68 Q38,62 50,68 Q62,62 72,68 Q62,78 50,72 Q38,78 28,68 Z' fill='%230B0A0F'/%3E%3C/svg%3E");
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 0;
            display: block !important;
        }
        html.dark.halloween .card-shadow:hover::after {
            opacity: 0.28 !important;
            transform: scale(1.12) rotate(4deg);
            filter: drop-shadow(0 0 12px rgba(249, 115, 22, 0.6));
        }

        /* --- 2. LIGHT MODE ("Warm Obsidian & Linen") --- */
        :root[data-theme="festive-light-premium"],
        :root[data-theme="halloween-light"],
        html[data-theme="halloween-light"],
        html.halloween:not(.dark) {
            --bg-main: #FBF9F6;
            --bg-card: #FFFFFF;
            --border-card: #EAE6DF;
            --text-heading: #121118;
            --text-body: #524E59;
            --text-muted: #8C8494;
            --accent-primary: #C2410C; /* Burnt Copper */
            --accent-secondary: #581C87; /* Deep Plum */
            --shadow-card: 0 1px 2px rgba(0,0,0,0.03), 0 8px 16px -4px rgba(0,0,0,0.04);

            --bg-app: #FBF9F6;
            --bg-surface: #FFFFFF;
            --bg-surface-subtle: #F7F5F0;
            --border-color: #EAE6DF;
            
            --text-primary: #121118;
            --text-secondary: #524E59;
            --text-muted: #8C8494;
            
            --accent-orange: #C2410C;
            --accent-purple: #581C87;
            
            --badge-orange-bg: rgba(194, 65, 12, 0.08);
            --badge-orange-text: #C2410C;
            --badge-purple-bg: rgba(88, 28, 135, 0.08);
            --badge-purple-text: #581C87;

            --color-bg-primary: #FFFFFF;
            --color-bg-secondary: #FBF9F6;
            --color-text-primary: #121118;
            --color-text-secondary: #524E59;
            --color-border-primary: #EAE6DF;
            --color-accent-halloween: #C2410C;
        }

        html.halloween:not(.dark) body,
        html[data-theme="halloween-light"] body {
            background-color: #FBF9F6 !important;
            background-image:
                radial-gradient(circle at 50% 0%, rgba(194, 65, 12, 0.03) 0%, transparent 60%),
                radial-gradient(circle at 90% 10%, rgba(88, 28, 135, 0.02) 0%, transparent 45%) !important;
            background-attachment: fixed !important;
            color: #121118 !important;
        }
        html.halloween:not(.dark) main,
        html[data-theme="halloween-light"] main {
            background-color: transparent !important;
        }

        /* Base Card Surface, Borders & Shadows in Light Mode */
        html.halloween:not(.dark) .card-shadow,
        html.halloween:not(.dark) .project-tile,
        html.halloween:not(.dark) .kanban-card,
        html[data-theme="halloween-light"] .card-shadow,
        html[data-theme="halloween-light"] .project-tile,
        html[data-theme="halloween-light"] .kanban-card {
            background-color: #FFFFFF !important;
            border: 1px solid #EAE6DF !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02), 0 8px 24px rgba(0,0,0,0.04) !important;
            color: #121118 !important;
        }
        html.halloween:not(.dark) .card-shadow:hover,
        html.halloween:not(.dark) .project-tile:hover,
        html[data-theme="halloween-light"] .card-shadow:hover,
        html[data-theme="halloween-light"] .project-tile:hover {
            border-color: #C2410C !important;
            box-shadow: 0 4px 20px rgba(194, 65, 12, 0.08) !important;
        }
        html.halloween:not(.dark) .glass,
        html[data-theme="halloween-light"] .glass {
            background: rgba(255, 255, 255, 0.85) !important;
            backdrop-filter: blur(16px) !important;
            -webkit-backdrop-filter: blur(16px) !important;
            border-color: #EAE6DF !important;
        }

        /* Light Mode High-Contrast Typography */
        html.halloween:not(.dark) h1,
        html.halloween:not(.dark) h2,
        html.halloween:not(.dark) h3,
        html.halloween:not(.dark) h4,
        html.halloween:not(.dark) .project-tile h1,
        html.halloween:not(.dark) .project-tile h2,
        html.halloween:not(.dark) .project-tile h3,
        html.halloween:not(.dark) .project-tile h4,
        html.halloween:not(.dark) .project-tile span.truncate,
        html[data-theme="halloween-light"] h1,
        html[data-theme="halloween-light"] h2,
        html[data-theme="halloween-light"] h3,
        html[data-theme="halloween-light"] h4,
        html[data-theme="halloween-light"] .project-tile h1,
        html[data-theme="halloween-light"] .project-tile h2,
        html[data-theme="halloween-light"] .project-tile h3,
        html[data-theme="halloween-light"] .project-tile h4,
        html[data-theme="halloween-light"] .project-tile span.truncate {
            color: #121118 !important;
        }
        html.halloween:not(.dark) .text-gray-400,
        html.halloween:not(.dark) .text-slate-500,
        html.halloween:not(.dark) .text-gray-500,
        html[data-theme="halloween-light"] .text-gray-400,
        html[data-theme="halloween-light"] .text-slate-500,
        html[data-theme="halloween-light"] .text-gray-500 {
            color: #524E59 !important;
        }
        html.halloween:not(.dark) .text-gray-700,
        html.halloween:not(.dark) .text-gray-600,
        html.halloween:not(.dark) .text-slate-300,
        html[data-theme="halloween-light"] .text-gray-700,
        html[data-theme="halloween-light"] .text-gray-600,
        html[data-theme="halloween-light"] .text-slate-300 {
            color: #121118 !important;
        }

        /* Light Mode Primary & Secondary Accents */
        html.halloween:not(.dark) .nav-active,
        .halloween:not(.dark) .nav-active {
            color: #C2410C !important;
            background: rgba(194, 65, 12, 0.08) !important;
            border: 1px solid rgba(194, 65, 12, 0.18) !important;
            border-radius: 9999px !important;
            border-bottom: 1px solid rgba(194, 65, 12, 0.18) !important;
            backdrop-filter: blur(8px) !important;
            font-weight: 600 !important;
        }
        html.halloween:not(.dark) .bg-blue-600,
        html.halloween:not(.dark) .bg-blue-500,
        .halloween:not(.dark) .bg-blue-600,
        .halloween:not(.dark) .bg-blue-500 {
            background-color: #C2410C !important;
            color: #FFFFFF !important;
        }
        html.halloween:not(.dark) .text-blue-600,
        html.halloween:not(.dark) .text-blue-500,
        .halloween:not(.dark) .text-blue-600,
        .halloween:not(.dark) .text-blue-500 {
            color: #C2410C !important;
        }
        html.halloween:not(.dark) .bg-blue-50,
        .halloween:not(.dark) .bg-blue-50 {
            background-color: rgba(194, 65, 12, 0.08) !important;
            color: #C2410C !important;
        }

        /* Card watermark: Warm Harvest Pumpkin (Light Mode) */
        html.halloween:not(.dark) .card-shadow::after,
        html[data-theme="halloween-light"] .card-shadow::after {
            content: '';
            position: absolute;
            bottom: -4px;
            right: 8px;
            width: 60px;
            height: 60px;
            pointer-events: none;
            opacity: 0.1;
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Cpath fill='%23C2410C' d='M50,15 C45,5 35,5 32,8 C38,14 42,15 45,18 C30,16 10,28 10,55 C10,80 30,92 50,92 C70,92 90,80 90,55 C90,28 70,16 55,18 C58,15 62,14 68,8 C65,5 55,5 50,15 Z'/%3E%3Cpolygon points='32,45 42,48 36,36' fill='%23FFFFFF'/%3E%3Cpolygon points='68,45 58,48 64,36' fill='%23FFFFFF'/%3E%3Cpolygon points='50,50 45,58 55,58' fill='%23FFFFFF'/%3E%3Cpath d='M28,68 Q38,62 50,68 Q62,62 72,68 Q62,78 50,72 Q38,78 28,68 Z' fill='%23FFFFFF'/%3E%3C/svg%3E");
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 0;
            display: block !important;
        }
        html.halloween:not(.dark) .card-shadow:hover::after,
        html[data-theme="halloween-light"] .card-shadow:hover::after {
            opacity: 0.25 !important;
            transform: scale(1.12) rotate(4deg);
            filter: drop-shadow(0 0 10px rgba(194, 65, 12, 0.4));
        }

        /* --- 3. REFINED NAVIGATION BAR STYLES --- */
        html.dark.halloween nav {
            background-color: rgba(11, 10, 15, 0.8) !important;
            backdrop-filter: blur(20px) !important;
            -webkit-backdrop-filter: blur(20px) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
            box-shadow: 0 1px 0 rgba(255, 255, 255, 0.02) !important;
        }
        html.dark.halloween nav a:not(.font-semibold):hover {
            background-color: rgba(255, 255, 255, 0.06) !important;
            color: #F4F0F8 !important;
        }

        html.halloween:not(.dark) nav,
        html[data-theme="halloween-light"] nav {
            background: rgba(251, 249, 246, 0.85) !important;
            backdrop-filter: blur(20px) !important;
            -webkit-backdrop-filter: blur(20px) !important;
            border-bottom: 1px solid #EAE6DF !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02) !important;
        }

        /* Navigation Link Typography & Smooth Transitions */
        html.dark.halloween nav .nav-item-inactive,
        html.dark.halloween nav a:not(.nav-item-active):not([href*="settings"]):not([class*="h-7"]) {
            color: #A198AC !important;
            transition: all 0.2s ease !important;
            border-radius: 9999px !important;
        }
        html.dark.halloween nav .nav-item-inactive:hover,
        html.dark.halloween nav a:not(.nav-item-active):not([href*="settings"]):not([class*="h-7"]):hover {
            color: #F4F0F8 !important;
            background: rgba(255, 255, 255, 0.06) !important;
        }

        html.halloween:not(.dark) nav .nav-item-inactive,
        html.halloween:not(.dark) nav a:not(.nav-item-active):not([href*="settings"]):not([class*="h-7"]),
        html[data-theme="halloween-light"] nav .nav-item-inactive,
        html[data-theme="halloween-light"] nav a:not(.nav-item-active):not([href*="settings"]):not([class*="h-7"]) {
            color: #524E59 !important;
            opacity: 1 !important;
            font-weight: 500 !important;
            transition: all 0.2s ease !important;
            border-radius: 9999px !important;
        }
        html.halloween:not(.dark) nav .nav-item-inactive:hover,
        html[data-theme="halloween-light"] nav .nav-item-inactive:hover {
            color: #121118 !important;
            background-color: rgba(0, 0, 0, 0.04) !important;
        }

        /* Top Navigation User Icons & Buttons */
        html.halloween:not(.dark) nav button,
        html.halloween:not(.dark) nav button svg,
        html.halloween:not(.dark) nav a[href*="settings"],
        html.halloween:not(.dark) nav a[href*="settings"] svg,
        html.halloween:not(.dark) nav form button,
        html.halloween:not(.dark) nav form button svg,
        html[data-theme="halloween-light"] nav button,
        html[data-theme="halloween-light"] nav button svg,
        html[data-theme="halloween-light"] nav a[href*="settings"],
        html[data-theme="halloween-light"] nav a[href*="settings"] svg,
        html[data-theme="halloween-light"] nav form button,
        html[data-theme="halloween-light"] nav form button svg {
            color: #121118 !important;
            transition: all 0.2s ease !important;
        }
        html.halloween:not(.dark) nav button:hover,
        html.halloween:not(.dark) nav a[href*="settings"]:hover,
        html.halloween:not(.dark) nav form button:hover,
        html[data-theme="halloween-light"] nav button:hover,
        html[data-theme="halloween-light"] nav a[href*="settings"]:hover,
        html[data-theme="halloween-light"] nav form button:hover {
            color: #C2410C !important;
            background-color: rgba(194, 65, 12, 0.06) !important;
        }

        /* --- 3b. REFINED BADGES & BUTTONS --- */
        html.halloween:not(.dark) .badge-retainer,
        html[data-theme="halloween-light"] .badge-retainer {
            background: rgba(14, 116, 144, 0.08) !important;
            color: #0E7490 !important;
            border: 1px solid rgba(14, 116, 144, 0.18) !important;
        }

        html.halloween:not(.dark) .badge-campaign,
        html[data-theme="halloween-light"] .badge-campaign {
            background: rgba(88, 28, 135, 0.08) !important;
            color: #581C87 !important;
            border: 1px solid rgba(88, 28, 135, 0.18) !important;
        }

        html.halloween:not(.dark) .badge-pitch,
        html[data-theme="halloween-light"] .badge-pitch {
            background: rgba(194, 65, 12, 0.08) !important;
            color: #C2410C !important;
            border: 1px solid rgba(194, 65, 12, 0.18) !important;
        }

        html.halloween:not(.dark) .badge-job-number,
        html[data-theme="halloween-light"] .badge-job-number {
            background: rgba(88, 28, 135, 0.06) !important;
            color: #581C87 !important;
            border: 1px solid rgba(88, 28, 135, 0.14) !important;
        }

        html.halloween:not(.dark) .badge-due,
        html.halloween:not(.dark) .badge-due-warning,
        html[data-theme="halloween-light"] .badge-due,
        html[data-theme="halloween-light"] .badge-due-warning {
            background: rgba(217, 119, 6, 0.08) !important;
            color: #B45309 !important;
            border: 1px solid rgba(217, 119, 6, 0.18) !important;
        }

        html.halloween:not(.dark) .badge-due-today,
        html[data-theme="halloween-light"] .badge-due-today {
            background: rgba(194, 65, 12, 0.08) !important;
            color: #C2410C !important;
            border: 1px solid rgba(194, 65, 12, 0.18) !important;
        }

        html.halloween:not(.dark) .badge-due-urgent,
        html[data-theme="halloween-light"] .badge-due-urgent {
            background: rgba(220, 38, 38, 0.08) !important;
            color: #B91C1C !important;
            border: 1px solid rgba(220, 38, 38, 0.18) !important;
        }

        html.halloween:not(.dark) .badge-due-normal,
        html[data-theme="halloween-light"] .badge-due-normal {
            background: rgba(0, 0, 0, 0.03) !important;
            color: #524E59 !important;
            border: 1px solid #EAE6DF !important;
        }

        /* View Board Buttons */
        html.halloween:not(.dark) .view-board-btn,
        html[data-theme="halloween-light"] .view-board-btn {
            background-color: #FFFFFF !important;
            color: #121118 !important;
            border: 1px solid #EAE6DF !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02) !important;
            transition: all 0.2s ease !important;
        }

        html.halloween:not(.dark) .project-tile:hover .view-board-btn,
        html.halloween:not(.dark) .group:hover .view-board-btn,
        html[data-theme="halloween-light"] .project-tile:hover .view-board-btn,
        html[data-theme="halloween-light"] .group:hover .view-board-btn {
            background-color: #C2410C !important;
            color: #FFFFFF !important;
            border-color: #C2410C !important;
            box-shadow: 0 2px 8px rgba(194, 65, 12, 0.2) !important;
        }

        /* Primary Action & Secondary Buttons */
        html.halloween:not(.dark) .btn-new-project,
        html[data-theme="halloween-light"] .btn-new-project {
            background-color: #18181B !important;
            color: #FFFFFF !important;
            font-weight: 600 !important;
            border: 1px solid rgba(0, 0, 0, 0.1) !important;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.2), 0 1px 2px rgba(0, 0, 0, 0.05) !important;
            transition: all 0.2s ease !important;
        }

        html.halloween:not(.dark) .btn-new-project:hover,
        html[data-theme="halloween-light"] .btn-new-project:hover {
            background-color: #C2410C !important;
            color: #FFFFFF !important;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.2), 0 4px 12px rgba(194, 65, 12, 0.2) !important;
        }

        html.dark.halloween .btn-new-project {
            background-color: #F97316 !important;
            color: #0B0A0F !important;
            font-weight: 700 !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.35), 0 0 20px rgba(249, 115, 22, 0.25) !important;
            transition: all 0.2s ease !important;
        }
        html.dark.halloween .btn-new-project:hover {
            background-color: #EA580C !important;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.35), 0 0 28px rgba(249, 115, 22, 0.4) !important;
        }

        html.halloween:not(.dark) .btn-edit-brand,
        html.halloween:not(.dark) .btn-secondary,
        html[data-theme="halloween-light"] .btn-edit-brand,
        html[data-theme="halloween-light"] .btn-secondary {
            background-color: #FFFFFF !important;
            color: #121118 !important;
            border: 1px solid #EAE6DF !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02) !important;
            transition: all 0.2s ease !important;
        }

        html.halloween:not(.dark) .btn-edit-brand:hover,
        html.halloween:not(.dark) .btn-secondary:hover,
        html[data-theme="halloween-light"] .btn-edit-brand:hover,
        html[data-theme="halloween-light"] .btn-secondary:hover {
            background-color: rgba(0, 0, 0, 0.04) !important;
            border-color: #D6D0C5 !important;
        }

        html.dark.halloween .btn-edit-brand,
        html.dark.halloween .btn-secondary {
            background-color: rgba(255, 255, 255, 0.03) !important;
            color: #F4F0F8 !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            transition: all 0.2s ease !important;
        }
        html.dark.halloween .btn-edit-brand:hover,
        html.dark.halloween .btn-secondary:hover {
            background-color: rgba(255, 255, 255, 0.06) !important;
            border-color: rgba(255, 255, 255, 0.18) !important;
        }

        /* --- 4. REFINED BRAND DIRECTORY PILLS --- */
        html.dark.halloween .brand-pill {
            background-color: rgba(22, 19, 31, 0.75) !important;
            backdrop-filter: blur(12px) !important;
            -webkit-backdrop-filter: blur(12px) !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3) !important;
        }
        html.dark.halloween .brand-pill:hover {
            border-color: #F97316 !important;
            box-shadow: 0 0 16px rgba(249, 115, 22, 0.25) !important;
            transform: translateY(-1px);
        }
        html.dark.halloween .brand-pill .brand-icon-box {
            background-color: rgba(11, 10, 15, 0.9) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
        }
        html.dark.halloween .brand-pill span {
            color: #F4F0F8 !important;
        }
        html.dark.halloween .brand-pill:hover span {
            color: #F97316 !important;
        }

        html.halloween:not(.dark) .brand-pill,
        html[data-theme="halloween-light"] .brand-pill {
            background-color: #FFFFFF !important;
            border: 1px solid #EAE6DF !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02) !important;
        }
        html.halloween:not(.dark) .brand-pill:hover,
        html[data-theme="halloween-light"] .brand-pill:hover {
            border-color: #C2410C !important;
            box-shadow: 0 4px 12px rgba(194, 65, 12, 0.08) !important;
            transform: translateY(-1px);
        }
        html.halloween:not(.dark) .brand-pill .brand-icon-box,
        html[data-theme="halloween-light"] .brand-pill .brand-icon-box {
            background-color: #F7F5F0 !important;
            border: 1px solid #EAE6DF !important;
        }
        html.halloween:not(.dark) .brand-pill span,
        html[data-theme="halloween-light"] .brand-pill span {
            color: #121118 !important;
        }
        html.halloween:not(.dark) .brand-pill:hover span,
        html[data-theme="halloween-light"] .brand-pill:hover span {
            color: #C2410C !important;
        }

        /* --- 5. SIDEBAR CARDS (Deadlines & Priority) --- */
        html.dark.halloween .deadlines-card,
        html.dark.halloween .sidebar-card {
            border-top: 1.5px solid #8B5CF6 !important;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5) !important;
        }
        html.halloween:not(.dark) .deadlines-card,
        html.halloween:not(.dark) .sidebar-card {
            border-top: 1.5px solid #581C87 !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02), 0 8px 24px rgba(0,0,0,0.04) !important;
        }

        /* =========================================================
           CHRISTMAS HOLIDAY THEME (Dual Dark & Light Mode)
           ========================================================= */

        /* --- 1. DARK MODE (Silent Night / Winter Midnight) --- */
        html.dark.christmas body {
            background-color: #06131A !important;
            background-image: 
                radial-gradient(circle at 85% 12%, rgba(16, 185, 129, 0.16) 0%, transparent 45%),
                radial-gradient(circle at 15% 88%, rgba(239, 68, 68, 0.14) 0%, transparent 45%),
                radial-gradient(circle at 50% 50%, rgba(56, 189, 248, 0.05) 0%, transparent 60%) !important;
            background-attachment: fixed !important;
            color: #F8FAFC !important;
        }
        html.dark.christmas main {
            background-color: transparent !important;
        }
        html.dark.christmas .card-shadow,
        .dark.christmas .card-shadow {
            background-color: #0E2330 !important;
            border-color: #1B3D54 !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.45), 0 0 1px rgba(56, 189, 248, 0.2) !important;
        }
        html.dark.christmas .card-shadow:hover,
        .dark.christmas .card-shadow:hover {
            border-color: rgba(239, 68, 68, 0.5) !important;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.6), 0 0 20px rgba(239, 68, 68, 0.25) !important;
        }
        html.dark.christmas [class*="dark:bg-[#111827]"],
        html.dark.christmas [class*="dark:bg-[#0F172A]"],
        .dark.christmas [class*="dark:bg-[#111827]"],
        .dark.christmas [class*="dark:bg-[#0F172A]"] {
            background-color: #0E2330 !important;
        }
        html.dark.christmas [class*="dark:border-slate-800"],
        html.dark.christmas [class*="dark:border-white/[0.05]"],
        html.dark.christmas [class*="dark:border-white/[0.06]"],
        .dark.christmas [class*="dark:border-slate-800"],
        .dark.christmas [class*="dark:border-white/[0.05]"],
        .dark.christmas [class*="dark:border-white/[0.06]"] {
            border-color: #1B3D54 !important;
        }
        html.dark.christmas .glass,
        .dark.christmas .glass {
            background: rgba(14, 35, 48, 0.9) !important;
            border-color: rgba(56, 189, 248, 0.25) !important;
        }

        /* Christmas Dark Mode Typography */
        html.dark.christmas h1,
        html.dark.christmas h2,
        html.dark.christmas h3,
        html.dark.christmas h4 {
            color: #F8FAFC !important;
        }
        html.dark.christmas .text-gray-400,
        html.dark.christmas .text-slate-500,
        html.dark.christmas .text-gray-500 {
            color: #94A3B8 !important;
        }
        html.dark.christmas .text-gray-700,
        html.dark.christmas .text-slate-300 {
            color: #E2E8F0 !important;
        }

        /* Christmas Dark Mode Primary Accents (Holiday Crimson & Pine Emerald) */
        html.dark.christmas .nav-active,
        .dark.christmas .nav-active {
            color: #F87171 !important;
            border-bottom: 2px solid #EF4444 !important;
        }
        html.dark.christmas .bg-blue-600,
        html.dark.christmas .bg-blue-500,
        .dark.christmas .bg-blue-600,
        .dark.christmas .bg-blue-500 {
            background-color: #EF4444 !important;
        }
        html.dark.christmas .text-blue-600,
        html.dark.christmas .text-blue-500,
        html.dark.christmas .dark\:text-blue-400,
        .dark.christmas .text-blue-600,
        .dark.christmas .text-blue-500,
        .dark.christmas .dark\:text-blue-400 {
            color: #F87171 !important;
        }
        html.dark.christmas .bg-blue-50,
        html.dark.christmas .dark\:bg-blue-500\/10,
        .dark.christmas .bg-blue-50,
        .dark.christmas .dark\:bg-blue-500\/10 {
            background-color: rgba(239, 68, 68, 0.15) !important;
            color: #FCA5A5 !important;
        }

        /* Christmas Dark Mode Tree Watermark */
        html.dark.christmas .card-shadow::after {
            content: '';
            position: absolute;
            bottom: 6px;
            right: 8px;
            width: 58px;
            height: 58px;
            pointer-events: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%2310B981' fill-opacity='0.08'%3E%3Cpath d='M12 2l2.5 5h-1.8l2.8 5h-2l3.5 6H7l3.5-6h-2l2.8-5h-1.8L12 2zm-1 18h2v2h-2v-2z'/%3E%3C/svg%3E");
            background-size: contain;
            background-repeat: no-repeat;
            background-position: right bottom;
            z-index: 0;
        }

        /* --- 2. LIGHT MODE (Winter Wonderland / Christmas Morning) --- */
        html.christmas:not(.dark) body {
            background-color: #F8FAFC !important;
            background-image:
                radial-gradient(circle at 90% 10%, rgba(239, 68, 68, 0.07) 0%, transparent 45%),
                radial-gradient(circle at 10% 90%, rgba(16, 185, 129, 0.06) 0%, transparent 45%) !important;
            background-attachment: fixed !important;
            color: #0F172A !important;
        }
        html.christmas:not(.dark) main {
            background-color: transparent !important;
        }
        html.christmas:not(.dark) .card-shadow {
            background-color: #FFFFFF !important;
            border-color: #E2E8F0 !important;
            box-shadow: 0 2px 10px rgba(15, 23, 42, 0.05), 0 1px 3px rgba(0, 0, 0, 0.04) !important;
        }
        html.christmas:not(.dark) .card-shadow:hover {
            border-color: #EF4444 !important;
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.12) !important;
        }
        html.christmas:not(.dark) .glass {
            background: rgba(255, 255, 255, 0.9) !important;
            border-color: rgba(239, 68, 68, 0.2) !important;
        }

        /* Christmas Light Mode Typography */
        html.christmas:not(.dark) h1,
        html.christmas:not(.dark) h2,
        html.christmas:not(.dark) h3,
        html.christmas:not(.dark) h4 {
            color: #0F172A !important;
        }
        html.christmas:not(.dark) .text-gray-400,
        html.christmas:not(.dark) .text-slate-500,
        html.christmas:not(.dark) .text-gray-500 {
            color: #64748B !important;
        }
        html.christmas:not(.dark) .text-gray-700,
        html.christmas:not(.dark) .text-slate-300 {
            color: #334155 !important;
        }

        /* Christmas Light Mode Primary Accents */
        html.christmas:not(.dark) .nav-active,
        .christmas:not(.dark) .nav-active {
            color: #DC2626 !important;
            border-bottom: 2px solid #DC2626 !important;
        }
        html.christmas:not(.dark) .bg-blue-600,
        html.christmas:not(.dark) .bg-blue-500,
        .christmas:not(.dark) .bg-blue-600,
        .christmas:not(.dark) .bg-blue-500 {
            background-color: #DC2626 !important;
        }
        html.christmas:not(.dark) .text-blue-600,
        html.christmas:not(.dark) .text-blue-500,
        .christmas:not(.dark) .text-blue-600,
        .christmas:not(.dark) .text-blue-500 {
            color: #DC2626 !important;
        }
        html.christmas:not(.dark) .bg-blue-50,
        .christmas:not(.dark) .bg-blue-50 {
            background-color: rgba(220, 38, 38, 0.1) !important;
            color: #991B1B !important;
        }

        /* Christmas Light Mode Tree Watermark */
        html.christmas:not(.dark) .card-shadow::after {
            content: '';
            position: absolute;
            bottom: 6px;
            right: 8px;
            width: 58px;
            height: 58px;
            pointer-events: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23DC2626' fill-opacity='0.05'%3E%3Cpath d='M12 2l2.5 5h-1.8l2.8 5h-2l3.5 6H7l3.5-6h-2l2.8-5h-1.8L12 2zm-1 18h2v2h-2v-2z'/%3E%3C/svg%3E");
            background-size: contain;
            background-repeat: no-repeat;
            background-position: right bottom;
            z-index: 0;
        }

        /* --- 3. CHRISTMAS NAVIGATION BAR STYLES --- */
        html.dark.christmas nav {
            background-color: rgba(10, 25, 38, 0.9) !important;
            border-bottom-color: #1B3D54 !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.45) !important;
        }
        html.dark.christmas nav a:not(.font-semibold):hover {
            background-color: rgba(16, 185, 129, 0.15) !important;
            color: #F8FAFC !important;
        }
        html.christmas:not(.dark) nav {
            background-color: rgba(248, 250, 252, 0.92) !important;
            border-bottom-color: #E2E8F0 !important;
            box-shadow: 0 2px 10px rgba(15, 23, 42, 0.05) !important;
        }
        html.christmas:not(.dark) nav a:not(.font-semibold):hover {
            background-color: rgba(220, 38, 38, 0.08) !important;
            color: #0F172A !important;
        }

        /* --- 4. CHRISTMAS BRAND DIRECTORY PILLS --- */
        html.dark.christmas .brand-pill {
            background-color: #0E2330 !important;
            border-color: #1B3D54 !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3) !important;
        }
        html.dark.christmas .brand-pill:hover {
            border-color: #EF4444 !important;
            box-shadow: 0 0 16px rgba(239, 68, 68, 0.3) !important;
            transform: translateY(-1px);
        }
        html.dark.christmas .brand-pill .brand-icon-box {
            background-color: #06131A !important;
            border: 1px solid #1E435D !important;
        }
        html.dark.christmas .brand-pill span {
            color: #F8FAFC !important;
        }
        html.dark.christmas .brand-pill:hover span {
            color: #F87171 !important;
        }

        html.christmas:not(.dark) .brand-pill {
            background-color: #FFFFFF !important;
            border-color: #E2E8F0 !important;
            box-shadow: 0 1px 4px rgba(15, 23, 42, 0.05) !important;
        }
        html.christmas:not(.dark) .brand-pill:hover {
            border-color: #DC2626 !important;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.15) !important;
            transform: translateY(-1px);
        }
        html.christmas:not(.dark) .brand-pill .brand-icon-box {
            background-color: #F8FAFC !important;
            border: 1px solid #E2E8F0 !important;
        }
        html.christmas:not(.dark) .brand-pill span {
            color: #0F172A !important;
        }
        html.christmas:not(.dark) .brand-pill:hover span {
            color: #DC2626 !important;
        }

        /* --- 5. CHRISTMAS KPI STAT CARDS --- */
        html.dark.christmas .stat-card {
            background-color: #0E2330 !important;
            border-color: #1B3D54 !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.45) !important;
        }
        html.dark.christmas .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 26px rgba(0, 0, 0, 0.6) !important;
        }
        html.dark.christmas .stat-assigned {
            border-top: 2px solid #38BDF8 !important;
            background: linear-gradient(180deg, rgba(56, 189, 248, 0.14) 0%, rgba(14, 35, 48, 0.95) 45%) !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.45), 0 -2px 10px rgba(56, 189, 248, 0.25) !important;
        }
        html.dark.christmas .stat-assigned .stat-label { color: #7DD3FC !important; }
        html.dark.christmas .stat-assigned .stat-subtext { color: #BAE6FD !important; }

        html.dark.christmas .stat-completed {
            border-top: 2px solid #10B981 !important;
            background: linear-gradient(180deg, rgba(16, 185, 129, 0.14) 0%, rgba(14, 35, 48, 0.95) 45%) !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.45), 0 -2px 12px rgba(16, 185, 129, 0.3) !important;
        }
        html.dark.christmas .stat-completed .stat-label { color: #10B981 !important; }
        html.dark.christmas .stat-completed .stat-subtext { color: #34D399 !important; }

        html.dark.christmas .stat-pending {
            border-top: 2px solid #F59E0B !important;
            background: linear-gradient(180deg, rgba(245, 158, 11, 0.14) 0%, rgba(14, 35, 48, 0.95) 45%) !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.45), 0 -2px 12px rgba(245, 158, 11, 0.3) !important;
        }
        html.dark.christmas .stat-pending .stat-label { color: #FBBF24 !important; }
        html.dark.christmas .stat-pending .stat-subtext { color: #FDE68A !important; }

        html.dark.christmas .stat-brands {
            border-top: 2px solid #EF4444 !important;
            background: linear-gradient(180deg, rgba(239, 68, 68, 0.16) 0%, rgba(14, 35, 48, 0.95) 45%) !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.45), 0 -2px 14px rgba(239, 68, 68, 0.35) !important;
        }
        html.dark.christmas .stat-brands .stat-label { color: #F87171 !important; }
        html.dark.christmas .stat-brands .stat-subtext { color: #FCA5A5 !important; }

        html.christmas:not(.dark) .stat-card {
            background-color: #FFFFFF !important;
            border-color: #E2E8F0 !important;
            box-shadow: 0 2px 10px rgba(15, 23, 42, 0.05), 0 1px 3px rgba(0, 0, 0, 0.04) !important;
        }
        html.christmas:not(.dark) .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.08) !important;
        }
        html.christmas:not(.dark) .stat-assigned {
            border-top: 2px solid #0284C7 !important;
            background: linear-gradient(180deg, rgba(2, 132, 199, 0.04) 0%, #FFFFFF 40%) !important;
        }
        html.christmas:not(.dark) .stat-assigned .stat-label { color: #0284C7 !important; }
        html.christmas:not(.dark) .stat-assigned .stat-subtext { color: #64748B !important; }

        html.christmas:not(.dark) .stat-completed {
            border-top: 2px solid #047857 !important;
            background: linear-gradient(180deg, rgba(4, 120, 87, 0.05) 0%, #FFFFFF 40%) !important;
        }
        html.christmas:not(.dark) .stat-completed .stat-label { color: #047857 !important; }
        html.christmas:not(.dark) .stat-completed .stat-subtext { color: #059669 !important; }

        html.christmas:not(.dark) .stat-pending {
            border-top: 2px solid #D97706 !important;
            background: linear-gradient(180deg, rgba(217, 119, 6, 0.05) 0%, #FFFFFF 40%) !important;
        }
        html.christmas:not(.dark) .stat-pending .stat-label { color: #D97706 !important; }
        html.christmas:not(.dark) .stat-pending .stat-subtext { color: #B45309 !important; }

        html.christmas:not(.dark) .stat-brands {
            border-top: 2px solid #DC2626 !important;
            background: linear-gradient(180deg, rgba(220, 38, 38, 0.06) 0%, #FFFFFF 40%) !important;
        }
        html.christmas:not(.dark) .stat-brands .stat-label { color: #DC2626 !important; }
        html.christmas:not(.dark) .stat-brands .stat-subtext { color: #B91C1C !important; }

        /* Christmas Sidebar Cards */
        html.dark.christmas .deadlines-card,
        html.dark.christmas .sidebar-card {
            border-top: 2px solid #EF4444 !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4), 0 -2px 10px rgba(239, 68, 68, 0.25) !important;
        }
        html.christmas:not(.dark) .deadlines-card,
        html.christmas:not(.dark) .sidebar-card {
            border-top: 2px solid #DC2626 !important;
            box-shadow: 0 2px 10px rgba(15, 23, 42, 0.05) !important;
        }

        /* Quill Editor Dark Mode & Pasted Content Color Fixes */
        .dark .ql-editor,
        .dark .ql-editor * {
            color: var(--color-text-primary) !important;
        }
        .dark .ql-editor.ql-blank::before {
            color: var(--color-text-secondary) !important;
            opacity: 0.5;
        }
    </style>
    <script>
        // Check for saved theme or default to system preference
        const halloweenEnabled = {{ \App\Services\FeatureManager::isHalloweenThemeEnabled() ? 'true' : 'false' }};
        const christmasEnabled = {{ \App\Services\FeatureManager::isChristmasThemeEnabled() ? 'true' : 'false' }};
        const activeThemeSetting = '{{ \App\Services\FeatureManager::getActiveTheme() }}';

        // Retrieve saved user preferences
        let savedTheme = localStorage.getItem('theme');
        let initialMode;

        if (savedTheme === 'light') {
            initialMode = 'light';
        } else if (savedTheme === 'dark' || savedTheme === 'halloween' || savedTheme === 'christmas') {
            initialMode = 'dark';
        } else {
            // Default according to platform setting or dark
            initialMode = (activeThemeSetting === 'light') ? 'light' : 'dark';
        }

        if (initialMode === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }

        if (halloweenEnabled) {
            document.documentElement.classList.add('halloween');
            document.documentElement.setAttribute('data-theme', initialMode === 'dark' ? 'halloween' : 'halloween-light');
        } else {
            document.documentElement.classList.remove('halloween');
            if (christmasEnabled) {
                document.documentElement.setAttribute('data-theme', initialMode === 'dark' ? 'christmas' : 'christmas-light');
            } else {
                document.documentElement.setAttribute('data-theme', initialMode);
            }
        }

        if (christmasEnabled) {
            document.documentElement.classList.add('christmas');
        } else {
            document.documentElement.classList.remove('christmas');
        }
        
        document.addEventListener('alpine:init', () => {
            Alpine.store('theme', {
                mode: initialMode, // 'light' or 'dark'
                halloween: halloweenEnabled,
                christmas: christmasEnabled,
                halloweenAvailable: halloweenEnabled,
                christmasAvailable: christmasEnabled,
                // Backward-compatibility helper
                get current() {
                    if (this.halloween) {
                        return this.mode === 'dark' ? 'halloween' : 'halloween-light';
                    }
                    if (this.christmas) {
                        return this.mode === 'dark' ? 'christmas' : 'christmas-light';
                    }
                    return this.mode;
                },
                setMode(mode) {
                    this.mode = mode;
                    localStorage.setItem('theme', mode);
                    this.apply();
                },
                set(theme) {
                    if (theme === 'halloween') {
                        this.halloween = true;
                        this.christmas = false;
                        this.mode = 'dark';
                        localStorage.setItem('theme', 'dark');
                    } else if (theme === 'christmas') {
                        this.christmas = true;
                        this.halloween = false;
                        this.mode = 'dark';
                        localStorage.setItem('theme', 'dark');
                    } else if (theme === 'dark') {
                        this.mode = 'dark';
                        this.halloween = false;
                        this.christmas = false;
                        localStorage.setItem('theme', 'dark');
                    } else {
                        this.mode = 'light';
                        this.halloween = false;
                        this.christmas = false;
                        localStorage.setItem('theme', 'light');
                    }
                    this.apply();
                },
                toggle() {
                    this.mode = (this.mode === 'dark') ? 'light' : 'dark';
                    localStorage.setItem('theme', this.mode);
                    this.apply();
                },
                apply() {
                    document.documentElement.classList.toggle('dark', this.mode === 'dark');
                    document.documentElement.classList.toggle('halloween', this.halloween);
                    document.documentElement.classList.toggle('christmas', this.christmas);
                    if (this.halloween) {
                        document.documentElement.setAttribute('data-theme', this.mode === 'dark' ? 'halloween' : 'halloween-light');
                    } else if (this.christmas) {
                        document.documentElement.setAttribute('data-theme', this.mode === 'dark' ? 'christmas' : 'christmas-light');
                    } else {
                        document.documentElement.setAttribute('data-theme', this.mode);
                    }
                }
            });

            Alpine.data('quillEditor', (initialValue) => ({
                content: initialValue,
                init() {
                    const quill = new Quill(this.$refs.editor, {
                        theme: 'snow',
                        placeholder: 'Start typing...',
                        modules: {
                            toolbar: [
                                [{ 'header': [1, 2, 3, false] }],
                                ['bold', 'italic', 'underline', 'strike'],
                                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                                ['clean']
                            ],
                            clipboard: {
                                matchers: [
                                    [Node.ELEMENT_NODE, function(node, delta) {
                                        delta.ops.forEach(op => {
                                             if (op.attributes) {
                                                 delete op.attributes.color;
                                                 delete op.attributes.background;
                                             }
                                        });
                                        return delta;
                                    }]
                                ]
                            }
                        }
                    });

                    // Set initial content if any
                    if (this.content) {
                        quill.clipboard.dangerouslyPasteHTML(this.content);
                    }

                    // Update hidden textarea on change
                    quill.on('text-change', () => {
                        this.content = quill.root.innerHTML === '<p><br></p>' ? '' : quill.root.innerHTML;
                    });
                }
            }));
        })
    </script>
</head>
<body class="antialiased bg-bg-secondary text-text-primary transition-colors duration-300"
      :class="{ 'dark': $store.theme.mode === 'dark', 'halloween': $store.theme.halloween, 'christmas': $store.theme.christmas }">
    <x-halloween-decorations />
    <x-christmas-decorations />
    <x-navigation />

    <main class="max-w-[1440px] mx-auto px-8 py-8">
        {{ $slot }}
    </main>
    <x-notification-panel />
    <x-toast />
    <x-confirm-modal />
    <x-highlighted-upload-overlay />
</body>
</html>
