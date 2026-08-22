@props(['tone' => 'neutral'])

@php
    $tones = [
        'success' => 'bg-success-soft text-success',
        'warning' => 'bg-warning-soft text-warning',
        'danger' => 'bg-danger-soft text-danger',
        'accent' => 'bg-accent-soft text-accent-ink',
        'neutral' => 'bg-surface-2 text-ink-2',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium '.($tones[$tone] ?? $tones['neutral'])]) }}>
    <span class="size-1.5 rounded-full bg-current opacity-70"></span>{{ $slot }}
</span>
