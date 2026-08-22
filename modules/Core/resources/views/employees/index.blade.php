<x-core::layouts.app :title="'Pegawai'">
    <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:py-10">
        <x-core::ui.page-header title="Pegawai" description="Pejabat struktural sesuai bagan organisasi 1 Juli 2026">
            <x-slot:actions>
                <span class="text-xs text-ink-3">
                    {{ $counts['employees'] }} pegawai · {{ $counts['positions'] }} posisi ·
                    {{ $counts['acting'] }} Plt · {{ $counts['vacant'] }} vacant
                </span>
            </x-slot:actions>
        </x-core::ui.page-header>

        <div class="divide-y divide-line rounded-xl bg-surface ring-1 ring-line">
            @foreach ($employees as $employee)
                <div class="flex flex-wrap items-center gap-x-4 gap-y-2 px-4 py-3 transition-colors duration-150 hover:bg-surface-2/60 sm:px-5">
                    <x-core::ui.avatar :name="$employee->name" />
                    <span class="w-48 truncate text-sm font-medium text-ink">{{ $employee->name }}</span>
                    <div class="flex min-w-0 flex-1 flex-wrap gap-1.5">
                        @foreach ($employee->activeAssignments as $assignment)
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-surface-2 px-2.5 py-0.5 text-xs text-ink-2">
                                {{ $assignment->position->name }}
                                @if ($assignment->is_acting)
                                    <span class="rounded bg-warning-soft px-1 py-px text-[9px] font-semibold uppercase text-warning">Plt</span>
                                @endif
                            </span>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-core::layouts.app>
