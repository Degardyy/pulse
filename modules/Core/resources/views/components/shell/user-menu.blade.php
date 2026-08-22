<div x-data="{ open: false }" class="relative">
    <button type="button" @click="open = !open" @click.outside="open = false"
            class="focusable flex items-center gap-2 rounded-lg p-1 transition-colors hover:bg-surface-2"
            aria-label="Menu akun">
        <x-core::ui.avatar :name="auth()->user()->name" />
        <x-core::ui.icon name="chevron-down" class="hidden size-3.5 text-ink-3 sm:block" />
    </button>

    <div x-show="open" x-cloak x-transition.origin.top.right.duration.150ms
         class="absolute right-0 z-40 mt-2 w-60 overflow-hidden rounded-xl bg-surface shadow-xl shadow-scrim/10 ring-1 ring-line">
        <div class="border-b border-line px-4 py-3">
            <p class="truncate text-sm font-semibold text-ink">{{ auth()->user()->name }}</p>
            <p class="truncate text-xs text-ink-3">{{ auth()->user()->email }}</p>
        </div>
        <div class="p-1.5">
            <button type="button" @click="$store.pulse.toggleTheme()"
                    class="focusable flex w-full items-center gap-2.5 rounded-lg px-2.5 py-2 text-sm text-ink-2 transition-colors hover:bg-surface-2 hover:text-ink">
                <x-core::ui.icon name="moon" class="size-4 dark:hidden" />
                <x-core::ui.icon name="sun" class="hidden size-4 dark:block" />
                <span class="dark:hidden">Mode gelap</span>
                <span class="hidden dark:inline">Mode terang</span>
            </button>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="focusable flex w-full items-center gap-2.5 rounded-lg px-2.5 py-2 text-sm text-danger transition-colors hover:bg-danger-soft">
                    <x-core::ui.icon name="logout" class="size-4" />
                    Keluar
                </button>
            </form>
        </div>
    </div>
</div>
