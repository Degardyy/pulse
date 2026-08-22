@php
    $unreadCount = auth()->user()->unreadNotifications()->count();
    $recentNotifications = auth()->user()->notifications()->latest()->limit(8)->get();
@endphp

<div x-data="{ open: false }" class="relative">
    <button type="button" @click="open = !open" @click.outside="open = false"
            aria-label="Notifikasi{{ $unreadCount ? " ({$unreadCount} belum dibaca)" : '' }}"
            class="focusable relative flex size-9 items-center justify-center rounded-lg text-ink-2 transition-colors hover:bg-surface-2">
        <x-core::ui.icon name="bell" class="size-5" />
        @if ($unreadCount > 0)
            <span class="absolute right-1.5 top-1.5 flex size-2 rounded-full bg-accent ring-2 ring-bg"></span>
        @endif
    </button>

    <div x-show="open" x-cloak x-transition.origin.top.right.duration.150ms
         class="absolute right-0 z-40 mt-2 w-[22rem] overflow-hidden rounded-xl bg-surface shadow-xl shadow-scrim/10 ring-1 ring-line">
        <div class="flex items-center justify-between border-b border-line px-4 py-3">
            <p class="text-sm font-semibold text-ink">Notifikasi</p>
            @if ($unreadCount > 0)
                <form method="POST" action="{{ route('core.notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="focusable rounded text-xs font-medium text-accent hover:text-accent-strong">
                        Tandai semua dibaca
                    </button>
                </form>
            @endif
        </div>

        @if ($recentNotifications->isEmpty())
            <x-core::ui.empty-state icon="bell" title="Tidak ada notifikasi"
                                    description="Persetujuan, tugas, dan pembaruan penting akan muncul di sini." />
        @else
            <div class="max-h-96 divide-y divide-line overflow-y-auto">
                @foreach ($recentNotifications as $notification)
                    @include('core::notifications.partials.row', ['notification' => $notification, 'compact' => true])
                @endforeach
            </div>
            <a href="{{ route('core.notifications.index') }}"
               class="focusable block border-t border-line px-4 py-2.5 text-center text-xs font-medium text-ink-2 transition-colors hover:bg-surface-2 hover:text-ink">
                Lihat semua notifikasi
            </a>
        @endif
    </div>
</div>
