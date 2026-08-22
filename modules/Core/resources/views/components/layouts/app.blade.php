@props(['title' => null, 'breadcrumbs' => []])

@php
    $user = auth()->user();
    $nav = app(\Modules\Core\Services\NavigationService::class);
    $navSections = $user ? $nav->sections($user) : [];
    $workspace = $user ? $nav->workspace($user) : null;
    $crumbs = $breadcrumbs !== [] ? $breadcrumbs : array_values(array_filter([
        ['label' => 'Beranda', 'url' => route('core.dashboard')],
        $title && $title !== 'Beranda' ? ['label' => $title] : null,
    ]));
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ? "$title · " : '' }}{{ config('app.name', 'PULSE') }}</title>

    {{-- Resolve theme before first paint to avoid a flash of the wrong mode. --}}
    <script>
        (() => {
            const t = localStorage.getItem('pulse.theme');
            if (t === 'dark' || (! t && matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-bg font-sans text-[13.5px] text-ink antialiased">
    <div x-data>
        <x-core::shell.sidebar :sections="$navSections" :workspace="$workspace" />

        <div class="min-h-screen md:pl-60" :class="$store.pulse.sidebarCollapsed ? 'md:pl-[60px]' : 'md:pl-60'"
             style="transition: padding 200ms var(--ease-out-soft)">
            <x-core::shell.topbar :breadcrumbs="$crumbs" />

            <main class="pb-24 md:pb-10">
                {{ $slot }}
            </main>
        </div>

        <x-core::shell.bottom-nav :items="$nav->primary($user)" />
        <x-core::shell.command-palette :sections="$navSections" />
        <x-core::shell.ai-panel />
    </div>
</body>
</html>
