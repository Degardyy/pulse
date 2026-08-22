<x-core::layouts.app :title="'Pengguna'">
    <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:py-10">
        <x-core::ui.page-header title="Pengguna" description="Administrasi akun PULSE — dikelola Department Information Technology">
            <x-slot:actions>
                @can('create', \Modules\Core\Models\User::class)
                    <a href="{{ route('core.admin.users.create') }}"
                       class="focusable inline-flex items-center gap-1.5 rounded-lg bg-accent px-3.5 py-2 text-sm font-semibold text-white transition-colors duration-150 hover:bg-accent-strong">
                        <x-core::ui.icon name="plus" class="size-4" />
                        Akun baru
                    </a>
                @endcan
            </x-slot:actions>
        </x-core::ui.page-header>

        @if (session('status'))
            <div class="mb-4 flex items-center gap-2.5 rounded-lg bg-success-soft px-4 py-3 text-sm text-success">
                <x-core::ui.icon name="check-circle" class="size-4.5" />
                {{ session('status') }}
            </div>
        @endif

        @if (session('generated_password'))
            <div class="mb-4 rounded-lg bg-warning-soft px-4 py-3 text-sm text-warning">
                Password sementara:
                <code class="rounded bg-surface px-2 py-0.5 font-mono font-semibold text-ink">{{ session('generated_password') }}</code>
                — catat dan sampaikan sekarang; tidak akan ditampilkan lagi.
            </div>
        @endif

        <div class="overflow-hidden rounded-xl bg-surface ring-1 ring-line">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-line text-left">
                            <th class="text-label px-5 py-3 font-semibold normal-case tracking-normal">Pengguna</th>
                            <th class="text-label px-5 py-3 font-semibold normal-case tracking-normal">Pegawai</th>
                            <th class="text-label px-5 py-3 font-semibold normal-case tracking-normal">Role</th>
                            <th class="text-label px-5 py-3 font-semibold normal-case tracking-normal">Status</th>
                            <th class="text-label px-5 py-3 font-semibold normal-case tracking-normal">Login terakhir</th>
                            <th class="px-5 py-3"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach ($users as $user)
                            <tr class="transition-colors duration-150 hover:bg-surface-2/60 {{ $user->is_active ? '' : 'opacity-55' }}">
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        <x-core::ui.avatar :name="$user->name" />
                                        <div class="min-w-0">
                                            <p class="truncate font-medium text-ink">{{ $user->name }}</p>
                                            <p class="truncate text-xs text-ink-3">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3 text-ink-2">{{ $user->employee?->name ?? '—' }}</td>
                                <td class="px-5 py-3">
                                    <div class="flex flex-wrap gap-1.5">
                                        @forelse ($user->roles->unique('id') as $role)
                                            <span class="rounded-full bg-accent-soft px-2.5 py-0.5 text-xs font-medium text-accent-ink">{{ $role->name }}</span>
                                        @empty
                                            <span class="text-xs text-ink-3">—</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3">
                                    <x-core::ui.status :tone="$user->is_active ? 'success' : 'neutral'">
                                        {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </x-core::ui.status>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3 text-ink-3">
                                    {{ $user->last_login_at?->format('d M Y H:i') ?? '—' }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        @can('update', $user)
                                            <a href="{{ route('core.admin.users.edit', $user) }}"
                                               class="focusable rounded-lg px-2.5 py-1.5 text-xs font-semibold text-accent hover:bg-accent-soft">Ubah</a>
                                        @endcan
                                        @can('resetPassword', $user)
                                            <form method="POST" action="{{ route('core.admin.users.reset-password', $user) }}">
                                                @csrf
                                                <button type="submit" class="focusable rounded-lg px-2.5 py-1.5 text-xs font-semibold text-ink-2 hover:bg-surface-2">Reset</button>
                                            </form>
                                        @endcan
                                        @can('toggleActive', $user)
                                            <form method="POST" action="{{ route('core.admin.users.toggle-active', $user) }}">
                                                @csrf
                                                <button type="submit"
                                                        class="focusable rounded-lg px-2.5 py-1.5 text-xs font-semibold {{ $user->is_active ? 'text-danger hover:bg-danger-soft' : 'text-success hover:bg-success-soft' }}">
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
