<template x-teleport="body">
    <div x-show="$store.pulse.aiOpen" x-cloak class="fixed inset-0 z-50" role="dialog" aria-modal="true" aria-label="PULSE AI">
        <div x-show="$store.pulse.aiOpen" x-transition.opacity.duration.150ms
             class="absolute inset-0 bg-scrim" @click="$store.pulse.aiOpen = false"></div>

        <aside x-show="$store.pulse.aiOpen" x-cloak
               x-transition:enter="transition duration-200" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
               x-transition:leave="transition duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
               class="absolute inset-y-0 right-0 flex w-full max-w-md flex-col bg-surface shadow-2xl shadow-scrim/20 ring-1 ring-line"
               @keydown.escape.window="$store.pulse.aiOpen = false">

            <header class="flex items-center gap-3 border-b border-line px-5 py-4">
                <span class="flex size-8 items-center justify-center rounded-lg bg-accent-soft text-accent">
                    <x-core::ui.icon name="sparkle" class="size-4.5" />
                </span>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-ink">PULSE AI</p>
                    <p class="text-xs text-ink-3">Asisten kerja Anda</p>
                </div>
                <button type="button" @click="$store.pulse.aiOpen = false" aria-label="Tutup"
                        class="focusable flex size-8 items-center justify-center rounded-lg text-ink-3 hover:bg-surface-2">
                    <x-core::ui.icon name="x" class="size-4.5" />
                </button>
            </header>

            <div class="flex-1 overflow-y-auto px-5 py-6">
                <p class="text-title">Apa yang bisa saya bantu hari ini?</p>
                <p class="mt-1 text-sm text-ink-2">AI memahami konteks unit kerja Anda dan hanya mengakses data sesuai izin Anda.</p>

                <p class="text-label mt-6 mb-2">Saran</p>
                <div class="space-y-1.5">
                    @foreach ([
                        'Analisis anggaran department saya',
                        'Ringkas tugas saya hari ini',
                        'Cari tiket IT yang belum selesai',
                        'Susun draf laporan bulanan',
                        'Jelaskan varian anggaran bulan ini',
                    ] as $prompt)
                        <button type="button" disabled
                                class="flex w-full items-center gap-2.5 rounded-lg bg-surface-2/60 px-3 py-2.5 text-left text-sm text-ink-2 opacity-80">
                            <x-core::ui.icon name="arrow-right" class="size-3.5 text-ink-3" />
                            {{ $prompt }}
                        </button>
                    @endforeach
                </div>

                <div class="mt-6 rounded-lg bg-accent-soft/60 px-3.5 py-3 text-xs leading-relaxed text-accent-ink">
                    Pratinjau antarmuka. Kemampuan AI diaktifkan pada tahap AI Foundation —
                    seluruh akses melalui service layer dengan otorisasi pengguna (ADR-005).
                </div>
            </div>

            <footer class="border-t border-line p-4">
                <div class="flex items-center gap-2 rounded-xl bg-surface-2/70 px-4 py-3">
                    <input type="text" disabled placeholder="Tanyakan sesuatu…"
                           class="w-full bg-transparent text-sm text-ink placeholder-ink-3 focus:outline-none">
                    <x-core::ui.icon name="send" class="size-4.5 text-ink-3" />
                </div>
            </footer>
        </aside>
    </div>
</template>
