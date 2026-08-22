@props(['item'])

@php $isActive = request()->routeIs($item['active']); @endphp

<a href="{{ route($item['route']) }}" title="{{ $item['label'] }}"
   @class([
       'focusable group flex items-center gap-3 rounded-lg px-2.5 py-2 text-sm transition-colors duration-150',
       'bg-accent-soft font-medium text-accent-ink' => $isActive,
       'text-ink-2 hover:bg-surface-2 hover:text-ink' => ! $isActive,
   ])
   @if ($isActive) aria-current="page" @endif>
    <x-core::ui.icon :name="$item['icon']" @class(['size-[18px]', 'text-accent' => $isActive, 'text-ink-3 group-hover:text-ink-2' => ! $isActive]) />
    <span class="truncate" x-show="! $store.pulse.sidebarCollapsed">{{ $item['label'] }}</span>
</a>
