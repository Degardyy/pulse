<x-core::layouts.app :title="'Masuk'">
    <div class="flex min-h-[70vh] items-center justify-center px-6 py-16">
        <div class="w-full max-w-md">
            <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
                <div class="mb-8 text-center">
                    <span class="mx-auto flex size-12 items-center justify-center rounded-xl bg-paljaya-500 text-2xl font-extrabold text-white">P</span>
                    <h1 class="mt-4 text-xl font-bold text-deep">Masuk ke PULSE</h1>
                    <p class="mt-1 text-sm text-slate-500">Gunakan akun pegawai Perumda Paljaya Anda</p>
                </div>

                <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="mb-1.5 block text-sm font-medium text-slate-700">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                               autocomplete="username"
                               class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:border-paljaya-500 focus:outline-none focus:ring-2 focus:ring-paljaya-200"
                               placeholder="nama@paljaya.co.id">
                        @error('email')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="mb-1.5 block text-sm font-medium text-slate-700">Password</label>
                        <input id="password" name="password" type="password" required autocomplete="current-password"
                               class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-800 focus:border-paljaya-500 focus:outline-none focus:ring-2 focus:ring-paljaya-200">
                        @error('password')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <label class="flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" name="remember"
                               class="size-4 rounded border-slate-300 text-paljaya-500 focus:ring-paljaya-200">
                        Ingat saya
                    </label>

                    <button type="submit"
                            class="w-full rounded-lg bg-paljaya-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-paljaya-600 focus:outline-none focus:ring-2 focus:ring-paljaya-300">
                        Masuk
                    </button>
                </form>
            </div>
            <p class="mt-6 text-center text-xs text-slate-400">
                Akun dikelola oleh administrator PULSE. Hubungi Department Information Technology bila mengalami kendala.
            </p>
        </div>
    </div>
</x-core::layouts.app>
