<x-core::layouts.guest>
    <div class="flex min-h-screen flex-col">
        <header class="mx-auto flex h-16 w-full max-w-5xl items-center justify-between px-6">
            <div class="flex items-center gap-2.5">
                <span class="flex size-8 items-center justify-center rounded-lg bg-accent text-sm font-bold text-white">P</span>
                <span class="text-[15px] font-bold tracking-tight text-ink">PULSE</span>
            </div>
            <a href="{{ route('login') }}"
               class="focusable rounded-lg bg-accent px-4 py-2 text-sm font-semibold text-white transition-colors duration-150 hover:bg-accent-strong">
                Masuk
            </a>
        </header>

        <main class="flex flex-1 items-center">
            <div class="mx-auto w-full max-w-5xl px-6 py-16">
                <p class="text-label">Perumda Paljaya</p>
                <h1 class="mt-3 max-w-2xl text-4xl font-bold leading-[1.15] tracking-tight text-ink sm:text-5xl">
                    Ruang kerja digital<br>untuk seluruh Paljaya.
                </h1>
                <p class="mt-5 max-w-xl text-base leading-relaxed text-ink-2">
                    PULSE menyatukan pekerjaan, persetujuan, dokumen, anggaran, pelaporan,
                    dan layanan AI setiap division dan department — dalam satu workplace
                    yang tenang, cerdas, dan terpadu.
                </p>
                <div class="mt-8 flex items-center gap-4">
                    <a href="{{ route('login') }}"
                       class="focusable inline-flex items-center gap-2 rounded-lg bg-accent px-5 py-2.5 text-sm font-semibold text-white transition-colors duration-150 hover:bg-accent-strong">
                        Masuk ke workspace
                        <x-core::ui.icon name="arrow-right" class="size-4" />
                    </a>
                    <span class="text-sm text-ink-3">Stage: {{ $stage }}</span>
                </div>

                <dl class="mt-16 grid max-w-lg grid-cols-3 gap-8 border-t border-line pt-8">
                    <div>
                        <dt class="text-label">Modul aktif</dt>
                        <dd class="mt-1 text-sm font-medium text-ink">{{ implode(', ', $modules) }}</dd>
                    </div>
                    <div>
                        <dt class="text-label">Arsitektur</dt>
                        <dd class="mt-1 text-sm font-medium text-ink">Modular monolith</dd>
                    </div>
                    <div>
                        <dt class="text-label">Prinsip</dt>
                        <dd class="mt-1 text-sm font-medium text-ink">Calm Enterprise</dd>
                    </div>
                </dl>
            </div>
        </main>

        <footer class="mx-auto w-full max-w-5xl px-6 py-6 text-xs text-ink-3">
            © {{ date('Y') }} Perumda Paljaya — Paljaya Ultimate Service Ecosystem
        </footer>
    </div>
</x-core::layouts.guest>
