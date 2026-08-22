<x-core::layouts.app :title="'Laporan'">
    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:py-10">
        <x-core::ui.page-header title="Laporan"
            description="Ekspor data PULSE sesuai hak akses Anda. Modul baru menambahkan laporannya sendiri di sini." />

        @if (empty($reports))
            <x-core::ui.panel :padding="false">
                <x-core::ui.empty-state icon="chart" title="Belum ada laporan untuk Anda" />
            </x-core::ui.panel>
        @else
            <div class="divide-y divide-line rounded-xl bg-surface ring-1 ring-line">
                @foreach ($reports as $key => $report)
                    <div class="flex flex-wrap items-center gap-4 px-5 py-4">
                        <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-surface-2 text-ink-3">
                            <x-core::ui.icon name="chart" class="size-4.5" />
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-ink">{{ $report['title'] }}</p>
                            <p class="mt-0.5 text-xs text-ink-3">{{ $report['description'] }}</p>
                        </div>
                        <a href="{{ route('core.reports.download', $key) }}"
                           class="focusable shrink-0 rounded-lg bg-accent-soft px-3.5 py-2 text-xs font-semibold text-accent-ink transition-colors hover:opacity-80">
                            Unduh CSV
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-core::layouts.app>
