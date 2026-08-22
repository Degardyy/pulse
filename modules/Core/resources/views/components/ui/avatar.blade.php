@props(['name', 'size' => 'md'])

@php
    $sizes = ['sm' => 'size-6 text-[10px]', 'md' => 'size-8 text-xs', 'lg' => 'size-10 text-sm'];
    $words = preg_split('/\s+/', trim($name));
    $initials = mb_strtoupper(mb_substr($words[0], 0, 1).(isset($words[1]) ? mb_substr($words[1], 0, 1) : ''));
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex shrink-0 items-center justify-center rounded-full bg-accent-soft font-semibold text-accent-ink '.($sizes[$size] ?? $sizes['md'])]) }}>
    {{ $initials }}
</span>
