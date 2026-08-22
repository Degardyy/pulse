@props(['padding' => true])

<div {{ $attributes->merge(['class' => 'rounded-xl bg-surface ring-1 ring-line '.($padding ? 'p-5 sm:p-6' : '')]) }}>
    {{ $slot }}
</div>
