<x-core::layouts.app :title="'Akun Baru'"
    :breadcrumbs="[
        ['label' => 'Beranda', 'url' => route('core.dashboard')],
        ['label' => 'Pengguna', 'url' => route('core.admin.users.index')],
        ['label' => 'Akun Baru'],
    ]">
    <div class="mx-auto max-w-xl px-4 py-8 sm:px-6 lg:py-10">
        <x-core::ui.page-header title="Akun Baru"
            description="Password sementara dibuat otomatis dan ditampilkan sekali setelah akun dibuat." />

        <form method="POST" action="{{ route('core.admin.users.store') }}" class="space-y-6">
            @csrf
            @include('core::admin.users.partials.form-fields')

            <div class="flex items-center gap-3 border-t border-line pt-6">
                <button type="submit"
                        class="focusable rounded-lg bg-accent px-4 py-2.5 text-sm font-semibold text-white transition-colors duration-150 hover:bg-accent-strong">
                    Buat Akun
                </button>
                <a href="{{ route('core.admin.users.index') }}"
                   class="focusable rounded-lg px-4 py-2.5 text-sm font-medium text-ink-2 transition-colors hover:bg-surface-2">Batal</a>
            </div>
        </form>
    </div>
</x-core::layouts.app>
