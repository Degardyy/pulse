<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($title) ? "$title · " : '' }}{{ config('app.name', 'PULSE') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-800 antialiased">
    <div class="flex min-h-screen flex-col">
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-6">
                <a href="{{ route('core.landing') }}" class="flex items-center gap-3">
                    <span class="flex size-9 items-center justify-center rounded-lg bg-paljaya-500 text-lg font-extrabold text-white">P</span>
                    <span class="leading-tight">
                        <span class="block text-base font-bold tracking-tight text-deep">PULSE</span>
                        <span class="block text-[11px] font-medium text-slate-500">Paljaya Ultimate Service Ecosystem</span>
                    </span>
                </a>
                {{ $header ?? '' }}
            </div>
        </header>

        <main class="flex-1">
            {{ $slot }}
        </main>

        <footer class="border-t border-slate-200 bg-white">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4 text-xs text-slate-500">
                <span>&copy; {{ date('Y') }} Perumda Paljaya</span>
                <span>PULSE — Enterprise Digital Workplace</span>
            </div>
        </footer>
    </div>
</body>
</html>
