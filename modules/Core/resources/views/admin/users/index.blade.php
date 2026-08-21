<x-core::layouts.app :title="'Pengguna'">
    <div class="mx-auto max-w-6xl px-6 py-10">
        <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-deep">Pengguna</h1>
                <p class="mt-1 text-sm text-slate-500">Administrasi akun PULSE — dikelola Department Information Technology</p>
            </div>
            @can('create', \Modules\Core\Models\User::class)
                <a href="{{ route('core.admin.users.create') }}"
                   class="rounded-lg bg-paljaya-500 px-4 py-2 text-sm font-semibold text-white hover:bg-paljaya-600">
                    + Akun Baru
                </a>
            @endcan
        </div>

        @if (session('status'))
            <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        @if (session('generated_password'))
            <div class="mb-4 rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                Password sementara: <code class="rounded bg-white px-2 py-0.5 font-mono font-semibold">{{ session('generated_password') }}</code>
                — catat dan sampaikan sekarang; password ini tidak akan ditampilkan lagi.
            </div>
        @endif

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead>
                        <tr class="bg-navy text-left text-xs font-semibold uppercase tracking-wide text-white">
                            <th class="px-6 py-3">Nama</th>
                            <th class="px-6 py-3">Email</th>
                            <th class="px-6 py-3">Pegawai</th>
                            <th class="px-6 py-3">Role</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3">Login Terakhir</th>
                            <th class="px-6 py-3"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($users as $user)
                            <tr class="hover:bg-slate-50 {{ $user->is_active ? '' : 'opacity-60' }}">
                                <td class="whitespace-nowrap px-6 py-3 font-medium text-slate-800">{{ $user->name }}</td>
                                <td class="whitespace-nowrap px-6 py-3 text-slate-600">{{ $user->email }}</td>
                                <td class="whitespace-nowrap px-6 py-3 text-slate-600">
                                    {{ $user->employee?->name ?? '—' }}
                                </td>
                                <td class="px-6 py-3">
                                    <div class="flex flex-wrap gap-1.5">
                                        @forelse ($user->roles->unique('id') as $role)
                                            <span class="rounded-full bg-paljaya-50 px-2.5 py-0.5 text-xs font-medium text-paljaya-700 ring-1 ring-paljaya-200">
                                                {{ $role->name }}
                                            </span>
                                        @empty
                                            <span class="text-xs text-slate-400">—</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-3">
                                    @if ($user->is_active)
                                        <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700 ring-1 ring-emerald-200">Aktif</span>
                                    @else
                                        <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-500 ring-1 ring-slate-200">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-6 py-3 text-slate-500">
                                    {{ $user->last_login_at?->format('d M Y H:i') ?? '—' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @can('update', $user)
                                            <a href="{{ route('core.admin.users.edit', $user) }}"
                                               class="rounded-lg px-2.5 py-1.5 text-xs font-semibold text-paljaya-600 hover:bg-paljaya-50">Ubah</a>
                                        @endcan
                                        @can('resetPassword', $user)
                                            <form method="POST" action="{{ route('core.admin.users.reset-password', $user) }}">
                                                @csrf
                                                <button type="submit"
                                                        class="rounded-lg px-2.5 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-100">
                                                    Reset Password
                                                </button>
                                            </form>
                                        @endcan
                                        @can('toggleActive', $user)
                                            <form method="POST" action="{{ route('core.admin.users.toggle-active', $user) }}">
                                                @csrf
                                                <button type="submit"
                                                        class="rounded-lg px-2.5 py-1.5 text-xs font-semibold {{ $user->is_active ? 'text-red-600 hover:bg-red-50' : 'text-emerald-600 hover:bg-emerald-50' }}">
                                                    {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                </button>
                                            </form>
                                        @endcan
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
