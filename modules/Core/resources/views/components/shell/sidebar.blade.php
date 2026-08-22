@props(['sections', 'workspace'])

{{-- Desktop sidebar: lightweight — no fill, just the page background and a hairline. --}}
<aside class="fixed inset-y-0 left-0 z-30 hidden flex-col border-r border-line bg-bg md:flex"
       :class="$store.pulse.sidebarCollapsed ? 'w-[60px]' : 'w-60'"
       style="transition: width 200ms var(--ease-out-soft)">

    {{-- Brand --}}
    <div class="flex h-14 items-center gap-2.5 px-3">
        <a href="{{ route('core.dashboard') }}" class="focusable flex items-center gap-2.5 rounded-lg">
            <span class="flex size-8 items-center justify-center rounded-lg bg-accent text-sm font-bold text-white">P</span>
            <span class="text-[15px] font-bold tracking-tight text-ink" x-show="! $store.pulse.sidebarCollapsed">PULSE</span>
        </a>
    </div>

    {{-- Workspace context --}}
    <div class="px-3 pb-2" x-show="! $store.pulse.sidebarCollapsed">
        <div class="rounded-lg bg-surface px-3 py-2 ring-1 ring-line">
            <p class="text-[10px] font-semibold uppercase tracking-[0.08em] text-ink-3">{{ $workspace['org'] }}</p>
            <p class="mt-0.5 truncate text-[13px] font-medium text-ink">{{ $workspace['context'] }}</p>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 space-y-5 overflow-y-auto px-3 py-3" aria-label="Navigasi utama">
        @foreach ($sections as $section)
            <div>
                <p class="text-label mb-1.5 px-2.5" x-show="! $store.pulse.sidebarCollapsed">{{ $section['label'] }}</p>
                <div class="space-y-0.5">
                    @foreach ($section['items'] as $item)
                        <x-core::shell.nav-item :item="$item" />
                    @endforeach
                </div>
            </div>
        @endforeach
    </nav>

    {{-- Footer: AI + collapse --}}
    <div class="space-y-0.5 border-t border-line px-3 py-3">
        <button type="button" @click="$store.pulse.aiOpen = true" title="PULSE AI"
                class="focusable flex w-full items-center gap-3 rounded-lg px-2.5 py-2 text-sm text-ink-2 transition-colors duration-150 hover:bg-surface-2 hover:text-ink">
            <x-core::ui.icon name="sparkle" class="size-[18px] text-accent" />
            <span x-show="! $store.pulse.sidebarCollapsed">PULSE AI</span>
        </button>
        <button type="button" @click="$store.pulse.toggleSidebar()"
                :title="$store.pulse.sidebarCollapsed ? 'Perlebar sidebar' : 'Ciutkan sidebar'"
                class="focusable flex w-full items-center gap-3 rounded-lg px-2.5 py-2 text-sm text-ink-3 transition-colors duration-150 hover:bg-surface-2 hover:text-ink-2">
            <x-core::ui.icon name="chevron-left" class="size-[18px] transition-transform duration-200"
                             ::class="$store.pulse.sidebarCollapsed && 'rotate-180'" />
            <span x-show="! $store.pulse.sidebarCollapsed">Ciutkan</span>
        </button>
    </div>
</aside>
