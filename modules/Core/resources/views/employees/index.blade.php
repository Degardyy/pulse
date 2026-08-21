<x-core::layouts.app :title="'Pegawai'">
    <div class="mx-auto max-w-6xl px-6 py-10">
        <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-deep">Pegawai</h1>
                <p class="mt-1 text-sm text-slate-500">Pejabat struktural sesuai bagan organisasi 1 Juli 2026</p>
            </div>
            <p class="text-sm text-slate-500">
                {{ $counts['employees'] }} pegawai · {{ $counts['positions'] }} posisi ·
                {{ $counts['acting'] }} Plt · {{ $counts['vacant'] }} vacant
            </p>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead>
                        <tr class="bg-navy text-left text-xs font-semibold uppercase tracking-wide text-white">
                            <th class="px-6 py-3">Nama</th>
                            <th class="px-6 py-3">Jabatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($employees as $employee)
                            <tr class="hover:bg-slate-50">
                                <td class="whitespace-nowrap px-6 py-3 font-medium text-slate-800">
                                    {{ $employee->name }}
                                </td>
                                <td class="px-6 py-3">
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach ($employee->activeAssignments as $assignment)
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-paljaya-50 px-2.5 py-1 text-xs font-medium text-paljaya-700 ring-1 ring-paljaya-200">
                                                {{ $assignment->position->name }}
                                                @if ($assignment->is_acting)
                                                    <span class="rounded bg-amber-100 px-1 py-px text-[10px] font-semibold uppercase text-amber-700">Plt</span>
                                                @endif
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-core::layouts.app>
