@props(['assignment'])

@if ($assignment)
    <span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 text-slate-700']) }}>
        {{ $assignment->employee->name }}
        @if ($assignment->is_acting)
            <span class="rounded bg-amber-100 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-amber-700">Plt</span>
        @endif
    </span>
@else
    <span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5']) }}>
        <span class="rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-500">Vacant</span>
    </span>
@endif
