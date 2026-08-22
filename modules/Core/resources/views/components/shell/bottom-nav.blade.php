@props(['items'])

{{-- Mobile bottom navigation --}}
<nav class="fixed inset-x-0 bottom-0 z-30 border-t border-line bg-surface/95 backdrop-blur md:hidden"
     aria-label="Navigasi bawah">
    <div class="mx-auto flex h-16 max-w-md items-stretch justify-around px-2 pb-[env(safe-area-inset-bottom)]">
        @foreach ($items as $item)
            @php $isActive = request()->routeIs($item['active']); @endphp
            <a href="{{ route($item['route']) }}"
               @class([
                   'focusable flex flex-1 flex-col items-center justify-center gap-1 rounded-lg text-[10px] font-medium',
                   'text-accent' => $isActive,
                   'text-ink-3' => ! $isActive,
               ])
               @if ($isActive) aria-current="page" @endif>
                <x-core::ui.icon :name="$item['icon']" class="size-5" />
                {{ $item['label'] }}
            </a>
        @endforeach
    </div>
</nav>
