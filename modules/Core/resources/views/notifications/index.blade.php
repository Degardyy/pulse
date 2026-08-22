<x-core::layouts.app :title="'Notifikasi'">
    <div class="mx-auto max-w-2xl px-4 py-8 sm:px-6 lg:py-10">
        <x-core::ui.page-header title="Notifikasi">
            <x-slot:actions>
                @if (auth()->user()->unreadNotifications()->count() > 0)
                    <form method="POST" action="{{ route('core.notifications.read-all') }}">
                        @csrf
                        <button type="submit"
                                class="focusable rounded-lg px-3 py-2 text-sm font-medium text-accent transition-colors hover:bg-accent-soft">
                            Tandai semua dibaca
                        </button>
                    </form>
                @endif
            </x-slot:actions>
        </x-core::ui.page-header>

        @if ($notifications->isEmpty())
            <x-core::ui.panel :padding="false">
                <x-core::ui.empty-state icon="bell" title="Tidak ada notifikasi"
                                        description="Persetujuan, tugas, dan pembaruan penting akan muncul di sini." />
            </x-core::ui.panel>
        @else
            <div class="divide-y divide-line rounded-xl bg-surface ring-1 ring-line">
                @foreach ($notifications as $notification)
                    @include('core::notifications.partials.row', ['notification' => $notification])
                @endforeach
            </div>

            <div class="mt-5 flex items-center justify-between text-sm">
                <p class="text-ink-3">
                    {{ $notifications->firstItem() }}–{{ $notifications->lastItem() }} dari {{ $notifications->total() }}
                </p>
                <div class="flex items-center gap-2">
                    @if (! $notifications->onFirstPage())
                        <a href="{{ $notifications->previousPageUrl() }}" class="focusable rounded-lg px-3 py-1.5 font-medium text-ink-2 hover:bg-surface-2">Sebelumnya</a>
                    @endif
                    @if ($notifications->hasMorePages())
                        <a href="{{ $notifications->nextPageUrl() }}" class="focusable rounded-lg px-3 py-1.5 font-medium text-ink-2 hover:bg-surface-2">Berikutnya</a>
                    @endif
                </div>
            </div>
        @endif
    </div>
</x-core::layouts.app>
