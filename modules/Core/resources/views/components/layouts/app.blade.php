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
            <div class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-6 px-6">
                <a href="{{ auth()->check() ? route('core.dashboard') : route('core.landing') }}" class="flex shrink-0 items-center gap-3">
                    <span class="flex size-9 items-center justify-center rounded-lg bg-paljaya-500 text-lg font-extrabold text-white">P</span>
                    <span class="leading-tight">
                        <span class="block text-base font-bold tracking-tight text-deep">PULSE</span>
                        <span class="block text-[11px] font-medium text-slate-500">Paljaya Ultimate Service Ecosystem</span>
                    </span>
                </a>

                @auth
                    <nav class="hidden flex-1 items-center gap-1 sm:flex">
                        <a href="{{ route('core.dashboard') }}"
                           class="rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('core.dashboard') ? 'bg-paljaya-50 text-paljaya-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            Dashboard
                        </a>
                        <a href="{{ route('core.organization.index') }}"
                           class="rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('core.organization.*') ? 'bg-paljaya-50 text-paljaya-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            Organisasi
                        </a>
                        <a href="{{ route('core.employees.index') }}"
                           class="rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('core.employees.*') ? 'bg-paljaya-50 text-paljaya-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            Pegawai
                        </a>
                    </nav>

                    <div x-data="{ open: false }" class="relative shrink-0">
                        <button @click="open = !open" @click.outside="open = false"
                                class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-100">
                            <span class="flex size-8 items-center justify-center rounded-full bg-navy text-xs font-bold text-white">
                                {{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                            </span>
                            <span class="hidden sm:block">{{ auth()->user()->name }}</span>
                            <svg class="size-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>
                        <div x-show="open" x-transition.opacity x-cloak
                             class="absolute right-0 z-20 mt-2 w-48 rounded-xl border border-slate-200 bg-white p-1.5 shadow-lg">
                            <div class="px-3 py-2">
                                <p class="truncate text-sm font-semibold text-slate-800">{{ auth()->user()->name }}</p>
                                <p class="truncate text-xs text-slate-500">{{ auth()->user()->email }}</p>
                            </div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                        class="w-full rounded-lg px-3 py-2 text-left text-sm font-medium text-red-600 hover:bg-red-50">
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="flex items-center gap-4">
                        {{ $header ?? '' }}
                        @if (! request()->routeIs('login'))
                            <a href="{{ route('login') }}"
                               class="rounded-lg bg-paljaya-500 px-4 py-2 text-sm font-semibold text-white hover:bg-paljaya-600">
                                Masuk
                            </a>
                        @endif
                    </div>
                @endauth
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
