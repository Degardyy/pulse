<x-core::layouts.app :title="'Beranda'">
    <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:py-10">

        {{-- Greeting --}}
        <div class="mb-10">
            <p class="text-sm text-ink-3">{{ $today }}</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-ink sm:text-[1.75rem]">
                {{ $greeting }}, {{ $firstName }}
            </h1>
            <p class="mt-1.5 text-sm text-ink-2">Berikut yang perlu perhatian Anda hari ini.</p>
        </div>

        {{-- Attention: quiet rows, tone through color only --}}
        <section class="mb-10" aria-labelledby="attention-label">
            <h2 id="attention-label" class="text-label mb-3">Perhatian</h2>
            <div class="divide-y divide-line rounded-xl bg-surface ring-1 ring-line">
                @foreach ($attention as $item)
                    <a href="{{ $item['url'] ?? '#' }}" class="focusable group flex items-center gap-3.5 px-4 py-3 transition-colors duration-150 first:rounded-t-xl last:rounded-b-xl hover:bg-surface-2/60">
                        <span @class([
                            'flex size-7 items-center justify-center rounded-lg',
                            'bg-accent-soft text-accent' => $item['tone'] === 'accent',
                            'bg-warning-soft text-warning' => $item['tone'] === 'warning',
                            'bg-danger-soft text-danger' => $item['tone'] === 'danger',
                        ])>
                            <x-core::ui.icon :name="$item['icon']" class="size-4" />
                        </span>
                        <span class="flex-1 text-sm text-ink">{{ $item['text'] }}</span>
                        <x-core::ui.icon name="chevron-right" class="size-4 text-ink-3 transition-transform duration-150 group-hover:translate-x-0.5" />
                    </a>
                @endforeach
            </div>
        </section>

        <div class="grid gap-x-10 gap-y-10 lg:grid-cols-[1fr_310px]">
            <div class="min-w-0 space-y-10">

                {{-- My work --}}
                <section aria-labelledby="mywork-label">
                    <div class="mb-3 flex items-baseline justify-between">
                        <h2 id="mywork-label" class="text-label">Pekerjaan Saya</h2>
                        <span class="text-xs text-ink-3">{{ count(array_filter($tasks, fn ($t) => ! $t['done'])) }} aktif</span>
                    </div>
                    <ul class="space-y-1">
                        @foreach ($tasks as $task)
                            <li class="group flex items-center gap-3 rounded-lg px-2 py-2 transition-colors duration-150 hover:bg-surface-2/70">
                                <span @class([
                                    'flex size-[18px] items-center justify-center rounded-full border',
                                    'border-success bg-success text-white' => $task['done'],
                                    'border-line-2 bg-surface' => ! $task['done'],
                                ])>
                                    @if ($task['done'])
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="size-3"><path d="m6 12.5 4 4 8-9" /></svg>
                                    @endif
                                </span>
                                <span @class(['flex-1 truncate text-sm', 'text-ink-3 line-through' => $task['done'], 'text-ink' => ! $task['done']])>
                                    {{ $task['label'] }}
                                </span>
                                <span @class([
                                    'text-xs',
                                    'text-warning' => $task['due'] === 'Hari ini',
                                    'text-ink-3' => $task['due'] !== 'Hari ini',
                                ])>{{ $task['due'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                </section>

                {{-- Recent --}}
                <section aria-labelledby="recent-label">
                    <h2 id="recent-label" class="text-label mb-3">Terakhir Dibuka</h2>
                    <ul class="space-y-1">
                        @foreach ($recents as $recent)
                            <li>
                                <a href="#" class="focusable flex items-center gap-3 rounded-lg px-2 py-2 transition-colors duration-150 hover:bg-surface-2/70">
                                    <x-core::ui.icon :name="$recent['icon']" class="size-[18px] text-ink-3" />
                                    <span class="flex-1 truncate text-sm text-ink">{{ $recent['label'] }}</span>
                                    <span class="text-xs text-ink-3">{{ $recent['meta'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            </div>

            {{-- Side column --}}
            <div class="space-y-6">

                {{-- Department / organization context --}}
                <section aria-labelledby="dept-label">
                    <h2 id="dept-label" class="text-label mb-3">{{ $department ? 'Department Saya' : 'Organisasi' }}</h2>
                    <x-core::ui.panel>
                        @if ($department)
                            <p class="text-title">{{ $department->name }}</p>
                            <p class="mt-0.5 text-xs text-ink-3">{{ $department->division->name }}</p>

                            <dl class="mt-5 space-y-4">
                                <div class="flex items-center justify-between">
                                    <dt class="text-sm text-ink-2">Kepala Department</dt>
                                    <dd class="text-sm font-medium text-ink">
                                        {{ $departmentHead?->employee->name ?? 'Vacant' }}
                                        @if ($departmentHead?->is_acting)
                                            <span class="ml-1 rounded bg-warning-soft px-1 py-0.5 text-[10px] font-semibold uppercase text-warning">Plt</span>
                                        @endif
                                    </dd>
                                </div>
                                <div class="flex items-center justify-between">
                                    <dt class="text-sm text-ink-2">Pejabat aktif</dt>
                                    <dd class="text-sm font-medium text-ink">{{ $departmentOfficials }}</dd>
                                </div>
                            </dl>
                        @else
                            <p class="text-title">Perumda Paljaya</p>
                            <p class="mt-0.5 text-xs text-ink-3">Struktur per 1 Juli 2026</p>
                            <dl class="mt-5 space-y-4">
                                @foreach ([
                                    ['label' => 'Direktorat', 'value' => $orgCounts['directorates'], 'url' => route('core.organization.index')],
                                    ['label' => 'Division & unit', 'value' => $orgCounts['divisions'], 'url' => route('core.organization.index')],
                                    ['label' => 'Department', 'value' => $orgCounts['departments'], 'url' => route('core.organization.index')],
                                    ['label' => 'Pejabat struktural', 'value' => $employeeCounts['employees'], 'url' => route('core.employees.index')],
                                ] as $row)
                                    <div class="flex items-center justify-between">
                                        <dt><a href="{{ $row['url'] }}" class="focusable rounded text-sm text-ink-2 hover:text-ink">{{ $row['label'] }}</a></dt>
                                        <dd class="text-sm font-semibold text-ink">{{ $row['value'] }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                            @if ($employeeCounts['vacant'] > 0)
                                <div class="mt-5 border-t border-line pt-4">
                                    <x-core::ui.status tone="warning">{{ $employeeCounts['vacant'] }} posisi vacant</x-core::ui.status>
                                </div>
                            @endif
                        @endif
                    </x-core::ui.panel>
                </section>

                {{-- AI insight --}}
                <section aria-labelledby="ai-label">
                    <h2 id="ai-label" class="text-label mb-3">Insight AI</h2>
                    <x-core::ui.panel class="relative overflow-hidden">
                        <div class="absolute -right-6 -top-6 size-24 rounded-full bg-accent-soft/70 blur-2xl"></div>
                        <div class="relative">
                            <x-core::ui.icon name="sparkle" class="size-5 text-accent" />
                            <p class="mt-3 text-sm leading-relaxed text-ink-2">“{{ $aiInsight }}”</p>
                            <button type="button" @click="$store.pulse.aiOpen = true"
                                    class="focusable mt-4 inline-flex items-center gap-1.5 rounded-lg text-sm font-medium text-accent transition-colors hover:text-accent-strong">
                                Buka PULSE AI
                                <x-core::ui.icon name="arrow-right" class="size-3.5" />
                            </button>
                        </div>
                    </x-core::ui.panel>
                </section>
            </div>
        </div>
    </div>
</x-core::layouts.app>
