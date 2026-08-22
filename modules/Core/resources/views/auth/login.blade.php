<x-core::layouts.guest :title="'Masuk'">
    <div class="flex min-h-screen">
        {{-- Brand side (desktop) --}}
        <div class="relative hidden w-1/2 flex-col justify-between overflow-hidden bg-[#0d1220] p-12 lg:flex">
            <div class="absolute -left-32 top-1/3 size-96 rounded-full bg-[#006eb6]/25 blur-3xl"></div>
            <div class="absolute -bottom-24 right-0 size-72 rounded-full bg-[#4aa3e0]/15 blur-3xl"></div>

            <div class="relative flex items-center gap-3">
                <span class="flex size-9 items-center justify-center rounded-lg bg-[#006eb6] text-base font-bold text-white">P</span>
                <span class="text-lg font-bold tracking-tight text-white">PULSE</span>
            </div>

            <div class="relative">
                <h1 class="max-w-md text-3xl font-bold leading-snug tracking-tight text-white">
                    Satu ruang kerja digital untuk seluruh Paljaya.
                </h1>
                <p class="mt-4 max-w-sm text-sm leading-relaxed text-white/60">
                    Pekerjaan, persetujuan, dokumen, anggaran, dan layanan AI —
                    dalam satu workplace yang tenang dan terpadu.
                </p>
            </div>

            <p class="relative text-xs text-white/40">© {{ date('Y') }} Perumda Paljaya — Paljaya Ultimate Service Ecosystem</p>
        </div>

        {{-- Form side --}}
        <div class="flex w-full items-center justify-center px-6 py-12 lg:w-1/2">
            <div class="w-full max-w-sm">
                <div class="mb-10 flex items-center gap-3 lg:hidden">
                    <span class="flex size-9 items-center justify-center rounded-lg bg-accent text-base font-bold text-white">P</span>
                    <span class="text-lg font-bold tracking-tight text-ink">PULSE</span>
                </div>

                <h2 class="text-display">Masuk ke PULSE</h2>
                <p class="mt-1.5 text-sm text-ink-2">Gunakan akun pegawai Perumda Paljaya Anda.</p>

                <form method="POST" action="{{ route('login.store') }}" class="mt-8 space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="mb-1.5 block text-sm font-medium text-ink">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                               class="focusable w-full rounded-lg bg-surface px-3.5 py-2.5 text-sm text-ink ring-1 ring-line-2 placeholder:text-ink-3 focus:ring-accent"
                               placeholder="nama@paljaya.co.id">
                        @error('email') <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="password" class="mb-1.5 block text-sm font-medium text-ink">Password</label>
                        <input id="password" name="password" type="password" required autocomplete="current-password"
                               class="focusable w-full rounded-lg bg-surface px-3.5 py-2.5 text-sm text-ink ring-1 ring-line-2 focus:ring-accent">
                        @error('password') <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
                    </div>

                    <label class="flex items-center gap-2 text-sm text-ink-2">
                        <input type="checkbox" name="remember" class="size-4 rounded border-line-2 text-accent focus:ring-accent">
                        Ingat saya
                    </label>

                    <button type="submit"
                            class="focusable w-full rounded-lg bg-accent px-4 py-2.5 text-sm font-semibold text-white transition-colors duration-150 hover:bg-accent-strong">
                        Masuk
                    </button>
                </form>

                <p class="mt-8 text-xs leading-relaxed text-ink-3">
                    Akun dikelola oleh Department Information Technology.
                    Hubungi IT bila mengalami kendala akses.
                </p>
            </div>
        </div>
    </div>
</x-core::layouts.guest>
