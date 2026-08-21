<x-core::layouts.app :title="'Akun Baru'">
    <div class="mx-auto max-w-2xl px-6 py-10">
        <div class="mb-8">
            <h1 class="text-2xl font-bold tracking-tight text-deep">Akun Baru</h1>
            <p class="mt-1 text-sm text-slate-500">
                Password sementara dibuat otomatis dan ditampilkan sekali setelah akun dibuat.
            </p>
        </div>

        <form method="POST" action="{{ route('core.admin.users.store') }}"
              class="space-y-6 rounded-xl border border-slate-200 bg-white p-8 shadow-sm">
            @csrf
            @include('core::admin.users.partials.form-fields')

            <div class="flex items-center gap-3 border-t border-slate-100 pt-6">
                <button type="submit"
                        class="rounded-lg bg-paljaya-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-paljaya-600">
                    Buat Akun
                </button>
                <a href="{{ route('core.admin.users.index') }}"
                   class="rounded-lg px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-100">Batal</a>
            </div>
        </form>
    </div>
</x-core::layouts.app>
