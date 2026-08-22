@props(['breadcrumbs' => []])

<header class="sticky top-0 z-20 border-b border-line bg-bg/85 backdrop-blur">
    <div class="flex h-14 items-center gap-3 px-4 sm:px-6">

        {{-- Mobile brand --}}
        <a href="{{ route('core.dashboard') }}" class="focusable flex items-center gap-2 md:hidden">
            <span class="flex size-7 items-center justify-center rounded-lg bg-accent text-xs font-bold text-white">P</span>
            <span class="text-sm font-bold tracking-tight text-ink">PULSE</span>
        </a>

        {{-- Breadcrumbs (desktop) --}}
        <nav class="hidden min-w-0 items-center gap-1.5 text-sm md:flex" aria-label="Breadcrumb">
            @foreach ($breadcrumbs as $crumb)
                @if (! $loop->first)
                    <x-core::ui.icon name="chevron-right" class="size-3.5 text-ink-3" />
                @endif
                @if (($crumb['url'] ?? null) && ! $loop->last)
                    <a href="{{ $crumb['url'] }}" class="focusable rounded text-ink-3 transition-colors hover:text-ink-2">{{ $crumb['label'] }}</a>
                @else
                    <span @class(['truncate', 'font-medium text-ink' => $loop->last, 'text-ink-3' => ! $loop->last])>{{ $crumb['label'] }}</span>
                @endif
            @endforeach
        </nav>

        <div class="flex-1"></div>

        {{-- Global search --}}
        <button type="button" @click="$store.pulse.paletteOpen = true"
                class="focusable hidden w-64 items-center gap-2.5 rounded-lg bg-surface px-3 py-1.5 text-sm text-ink-3 ring-1 ring-line transition-colors duration-150 hover:ring-line-2 sm:flex">
            <x-core::ui.icon name="search" class="size-4" />
            <span class="flex-1 text-left">Cari di PULSE…</span>
            <kbd class="rounded border border-line-2 bg-surface-2 px-1.5 py-0.5 font-sans text-[10px] font-medium text-ink-3">Ctrl K</kbd>
        </button>
        <button type="button" @click="$store.pulse.paletteOpen = true" aria-label="Cari"
                class="focusable flex size-9 items-center justify-center rounded-lg text-ink-2 transition-colors hover:bg-surface-2 sm:hidden">
            <x-core::ui.icon name="search" class="size-5" />
        </button>

        {{-- AI --}}
        <button type="button" @click="$store.pulse.aiOpen = true" aria-label="PULSE AI"
                class="focusable flex size-9 items-center justify-center rounded-lg text-accent transition-colors hover:bg-accent-soft">
            <x-core::ui.icon name="sparkle" class="size-5" />
        </button>

        {{-- Notifications --}}
        <x-core::shell.notifications />

        {{-- User --}}
        <x-core::shell.user-menu />
    </div>
</header>
