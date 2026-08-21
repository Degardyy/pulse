<x-core::layouts.app :title="'Struktur Organisasi'">
    <div class="mx-auto max-w-6xl px-6 py-10">
        <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-deep">Struktur Organisasi</h1>
                <p class="mt-1 text-sm text-slate-500">Perumda Paljaya — berlaku per 1 Juli 2026</p>
            </div>
            <p class="text-sm text-slate-500">
                {{ $counts['directorates'] }} direktorat · {{ $counts['divisions'] }} division/unit ·
                {{ $counts['departments'] }} department
            </p>
        </div>

        <div class="space-y-8">
            @foreach ($directorates as $directorate)
                <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <header class="bg-navy px-6 py-4">
                        <h2 class="font-bold text-white">{{ $directorate->name }}</h2>
                    </header>
                    <div class="grid gap-6 p-6 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($directorate->divisions as $division)
                            <div class="rounded-lg border border-slate-200 p-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-paljaya-500">
                                    {{ $division->type === \Modules\Core\Models\Division::TYPE_UNIT ? 'Unit' : 'Division' }}
                                    · {{ $division->code }}
                                </p>
                                <h3 class="mt-1 font-semibold text-slate-800">{{ $division->name }}</h3>
                                @if ($division->departments->isNotEmpty())
                                    <ul class="mt-3 space-y-1.5 border-t border-slate-100 pt-3">
                                        @foreach ($division->departments as $department)
                                            <li class="flex items-start gap-2 text-sm text-slate-600">
                                                <span class="mt-1.5 size-1.5 shrink-0 rounded-full bg-paljaya-300"></span>
                                                {{ $department->name }}
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    </div>
</x-core::layouts.app>
