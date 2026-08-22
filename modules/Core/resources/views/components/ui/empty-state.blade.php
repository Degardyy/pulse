@props(['icon' => 'inbox', 'title', 'description' => null])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center px-6 py-10 text-center']) }}>
    <span class="flex size-10 items-center justify-center rounded-full bg-surface-2 text-ink-3">
        <x-core::ui.icon :name="$icon" class="size-5" />
    </span>
    <p class="mt-3 text-sm font-medium text-ink">{{ $title }}</p>
    @if ($description)
        <p class="mt-1 max-w-xs text-sm text-ink-3">{{ $description }}</p>
    @endif
    {{ $slot }}
</div>
