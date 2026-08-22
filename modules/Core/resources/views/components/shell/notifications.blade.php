<div x-data="{ open: false }" class="relative">
    <button type="button" @click="open = !open" @click.outside="open = false" aria-label="Notifikasi"
            class="focusable flex size-9 items-center justify-center rounded-lg text-ink-2 transition-colors hover:bg-surface-2">
        <x-core::ui.icon name="bell" class="size-5" />
    </button>

    <div x-show="open" x-cloak x-transition.origin.top.right.duration.150ms
         class="absolute right-0 z-40 mt-2 w-80 overflow-hidden rounded-xl bg-surface shadow-xl shadow-scrim/10 ring-1 ring-line">
        <div class="flex items-center justify-between border-b border-line px-4 py-3">
            <p class="text-sm font-semibold text-ink">Notifikasi</p>
        </div>
        {{-- Notification engine arrives with the Core Notification iteration. --}}
        <x-core::ui.empty-state icon="bell" title="Tidak ada notifikasi"
                                description="Persetujuan, tugas, dan pembaruan penting akan muncul di sini." />
    </div>
</div>
