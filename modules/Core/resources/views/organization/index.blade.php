<x-core::layouts.app :title="'Struktur Organisasi'">
    <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:py-10">
        <x-core::ui.page-header title="Struktur Organisasi" description="Perumda Paljaya — berlaku per 1 Juli 2026">
            <x-slot:actions>
                <span class="text-xs text-ink-3">
                    {{ $counts['directorates'] }} direktorat · {{ $counts['divisions'] }} division/unit · {{ $counts['departments'] }} department
                </span>
            </x-slot:actions>
        </x-core::ui.page-header>

        <div class="space-y-12">
            @foreach ($directorates as $directorate)
                <section aria-label="{{ $directorate->name }}">
                    <div class="mb-4 flex flex-wrap items-baseline justify-between gap-2 border-b border-line pb-3">
                        <h2 class="text-title">{{ $directorate->name }}</h2>
                        @foreach ($directorate->positions as $position)
                            <span class="text-sm text-ink-2">
                                {{ $position->currentAssignment?->employee->name ?? 'Vacant' }}
                            </span>
                        @endforeach
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($directorate->divisions as $division)
                            <x-core::ui.panel :padding="false" class="p-4">
                                <p class="text-[10px] font-semibold uppercase tracking-[0.08em] text-accent">
                                    {{ $division->type === \Modules\Core\Models\Division::TYPE_UNIT ? 'Unit' : 'Division' }} · {{ $division->code }}
                                </p>
                                <h3 class="mt-1 text-sm font-semibold leading-snug text-ink">{{ $division->name }}</h3>

                                @foreach ($division->positions as $position)
                                    <p class="mt-1 flex flex-wrap items-center gap-1.5 text-[13px] text-ink-2">
                                        {{ $position->currentAssignment?->employee->name ?? 'Vacant' }}
                                        @if ($position->currentAssignment?->is_acting)
                                            <span class="rounded bg-warning-soft px-1 py-px text-[9px] font-semibold uppercase text-warning">Plt</span>
                                        @endif
                                        @if ($division->positions->count() > 1)
                                            <span class="text-[11px] text-ink-3">— {{ $position->name }}</span>
                                        @endif
                                    </p>
                                @endforeach

                                @if ($division->departments->isNotEmpty())
                                    <ul class="mt-3 space-y-2.5 border-t border-line pt-3">
                                        @foreach ($division->departments as $department)
                                            <li class="text-[13px] leading-snug">
                                                <span class="text-ink">{{ $department->name }}</span>
                                                @foreach ($department->positions as $position)
                                                    <span class="mt-0.5 flex flex-wrap items-center gap-1.5 text-xs text-ink-3">
                                                        {{ $position->currentAssignment?->employee->name ?? 'Vacant' }}
                                                        @if ($position->currentAssignment?->is_acting)
                                                            <span class="rounded bg-warning-soft px-1 py-px text-[9px] font-semibold uppercase text-warning">Plt</span>
                                                        @endif
                                                    </span>
                                                @endforeach
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </x-core::ui.panel>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    </div>
</x-core::layouts.app>
