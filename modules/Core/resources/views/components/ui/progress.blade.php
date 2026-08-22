@props(['value' => 0, 'tone' => 'accent'])

@php
    $tones = ['accent' => 'bg-accent', 'success' => 'bg-success', 'warning' => 'bg-warning', 'danger' => 'bg-danger'];
    $value = max(0, min(100, (float) $value));
@endphp

<div {{ $attributes->merge(['class' => 'h-1.5 w-full overflow-hidden rounded-full bg-surface-2']) }}
     role="progressbar" aria-valuenow="{{ $value }}" aria-valuemin="0" aria-valuemax="100">
    <div class="h-full rounded-full {{ $tones[$tone] ?? $tones['accent'] }} transition-[width] duration-500"
         style="width: {{ $value }}%"></div>
</div>
