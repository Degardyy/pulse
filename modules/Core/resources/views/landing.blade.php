<x-core::layouts.app>
    <x-slot:header>
        <span class="inline-flex items-center gap-1.5 rounded-full bg-paljaya-50 px-3 py-1 text-xs font-semibold text-paljaya-700 ring-1 ring-paljaya-200">
            <span class="size-1.5 rounded-full bg-paljaya-500"></span>
            Stage: {{ $stage }}
        </span>
    </x-slot:header>

    <section class="bg-gradient-to-b from-white to-slate-50">
        <div class="mx-auto max-w-6xl px-6 py-20 text-center">
            <p class="text-sm font-semibold uppercase tracking-widest text-paljaya-500">Perumda Paljaya</p>
            <h1 class="mt-3 text-4xl font-extrabold tracking-tight text-deep sm:text-5xl">
                Satu Ruang Kerja Digital<br class="hidden sm:block"> untuk Seluruh Paljaya
            </h1>
            <p class="mx-auto mt-5 max-w-2xl text-base leading-relaxed text-slate-600">
                PULSE menyatukan division, department, employee, workflow, document, reporting,
                dashboard, dan AI services dalam satu enterprise digital workplace.
            </p>
        </div>
    </section>

    <section class="mx-auto max-w-6xl px-6 pb-20">
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-sm font-bold uppercase tracking-wide text-navy">Modul Aktif</h2>
                <ul class="mt-4 space-y-2">
                    @foreach ($modules as $module)
                        <li class="flex items-center gap-2 text-sm text-slate-700">
                            <span class="size-2 rounded-full bg-emerald-500"></span>
                            {{ $module }}
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-sm font-bold uppercase tracking-wide text-navy">Arsitektur</h2>
                <p class="mt-4 text-sm leading-relaxed text-slate-600">
                    Modular monolith dengan PULSE Core sebagai foundation: RBAC, workflow,
                    document, audit trail, dan AI foundation dipakai bersama oleh setiap
                    division portal.
                </p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-sm font-bold uppercase tracking-wide text-navy">Tahap Berikutnya</h2>
                <p class="mt-4 text-sm leading-relaxed text-slate-600">
                    PULSE Core: authentication, organization, employee, role &amp; permission.
                    Lihat <span class="font-mono text-xs">docs/roadmap.md</span> untuk rencana lengkap.
                </p>
            </div>
        </div>
    </section>
</x-core::layouts.app>
