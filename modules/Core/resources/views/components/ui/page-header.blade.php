@props(['title', 'description' => null])

<div {{ $attributes->merge(['class' => 'mb-8 flex flex-wrap items-end justify-between gap-x-6 gap-y-4']) }}>
    <div class="min-w-0">
        <h1 class="text-display">{{ $title }}</h1>
        @if ($description)
            <p class="mt-1.5 text-sm text-ink-2">{{ $description }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="flex shrink-0 items-center gap-2">{{ $actions }}</div>
    @endisset
</div>
