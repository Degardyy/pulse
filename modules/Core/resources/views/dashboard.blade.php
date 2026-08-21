<x-core::layouts.app :title="'Dashboard'">
    <div class="mx-auto max-w-6xl px-6 py-10">
        <div class="mb-8">
            <h1 class="text-2xl font-bold tracking-tight text-deep">
                Selamat datang, {{ auth()->user()->name }}
            </h1>
            <p class="mt-1 text-sm text-slate-500">Ruang kerja digital Perumda Paljaya</p>
        </div>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Direktorat</p>
                <p class="mt-2 text-3xl font-bold text-paljaya-500">{{ $counts['directorates'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Division</p>
                <p class="mt-2 text-3xl font-bold text-paljaya-500">{{ $counts['divisions'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Department</p>
                <p class="mt-2 text-3xl font-bold text-paljaya-500">{{ $counts['departments'] }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Pejabat Struktural</p>
                <p class="mt-2 text-3xl font-bold text-paljaya-500">{{ $employeeCounts['employees'] }}</p>
                <p class="mt-1 text-xs text-slate-400">{{ $employeeCounts['vacant'] }} posisi vacant</p>
            </div>
        </div>

        <div class="mt-8 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-sm font-bold uppercase tracking-wide text-navy">Menu</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <a href="{{ route('core.organization.index') }}"
                   class="group rounded-lg border border-slate-200 p-4 transition hover:border-paljaya-300 hover:bg-paljaya-50">
                    <p class="font-semibold text-slate-800 group-hover:text-paljaya-700">Struktur Organisasi</p>
                    <p class="mt-1 text-sm text-slate-500">Direktorat, division, dan department Perumda Paljaya</p>
                </a>
                <a href="{{ route('core.employees.index') }}"
                   class="group rounded-lg border border-slate-200 p-4 transition hover:border-paljaya-300 hover:bg-paljaya-50">
                    <p class="font-semibold text-slate-800 group-hover:text-paljaya-700">Pegawai</p>
                    <p class="mt-1 text-sm text-slate-500">Pejabat struktural dan jabatannya</p>
                </a>
                @can('viewAny', \Modules\Core\Models\User::class)
                    <a href="{{ route('core.admin.users.index') }}"
                       class="group rounded-lg border border-slate-200 p-4 transition hover:border-paljaya-300 hover:bg-paljaya-50">
                        <p class="font-semibold text-slate-800 group-hover:text-paljaya-700">Pengguna</p>
                        <p class="mt-1 text-sm text-slate-500">Administrasi akun PULSE (Department IT)</p>
                    </a>
                @endcan
                <div class="rounded-lg border border-dashed border-slate-200 p-4 opacity-60">
                    <p class="font-semibold text-slate-500">Division Portal</p>
                    <p class="mt-1 text-sm text-slate-400">Segera — Stage 3</p>
                </div>
            </div>
        </div>
    </div>
</x-core::layouts.app>
