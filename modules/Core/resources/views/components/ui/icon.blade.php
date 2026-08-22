@props(['name'])

{{--
    PULSE icon set: a single hand-drawn stroke family (1.7px, round caps) so
    every icon shares the same visual weight. No external icon dependency.
--}}
<svg {{ $attributes->merge(['class' => 'size-5 shrink-0']) }} viewBox="0 0 24 24" fill="none"
     stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($name)
        @case('home')
            <path d="M3.5 10.6 12 4l8.5 6.6" /><path d="M5.8 9.8V20h12.4V9.8" /><path d="M10 20v-5h4v5" />
            @break
        @case('briefcase')
            <rect x="3.5" y="7.5" width="17" height="12" rx="2" /><path d="M9 7.5V6a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v1.5" /><path d="M3.5 12.5h17" />
            @break
        @case('building')
            <rect x="5" y="4" width="14" height="16" rx="1.5" /><path d="M9 8h2M13 8h2M9 12h2M13 12h2M9 16h2M13 16h2" /><path d="M3.5 20h17" />
            @break
        @case('users')
            <circle cx="9" cy="8.5" r="3" /><path d="M3.5 19c.6-3 2.8-4.5 5.5-4.5s4.9 1.5 5.5 4.5" /><circle cx="16.5" cy="9.5" r="2.3" /><path d="M16 14.6c2.3.1 4 1.4 4.5 3.9" />
            @break
        @case('shield')
            <path d="M12 3.5 18.5 6v5.4c0 4-2.6 6.9-6.5 8.9-3.9-2-6.5-4.9-6.5-8.9V6L12 3.5Z" /><path d="m9.3 11.6 2 2 3.4-3.7" />
            @break
        @case('search')
            <circle cx="11" cy="11" r="6.5" /><path d="m16 16 4.5 4.5" />
            @break
        @case('bell')
            <path d="M6.3 15.5v-5a5.7 5.7 0 0 1 11.4 0v5l1.6 2.6H4.7l1.6-2.6Z" /><path d="M10.2 20.5a2 2 0 0 0 3.6 0" />
            @break
        @case('sparkle')
            <path d="M12 4.5 13.6 9 18 10.6 13.6 12.2 12 16.7 10.4 12.2 6 10.6 10.4 9 12 4.5Z" /><path d="M18.5 15.5 19.2 17.4 21 18.1 19.2 18.8 18.5 20.7 17.8 18.8 16 18.1 17.8 17.4 18.5 15.5Z" />
            @break
        @case('chevron-left')
            <path d="m14.5 6-5.5 6 5.5 6" />
            @break
        @case('chevron-right')
            <path d="m9.5 6 5.5 6-5.5 6" />
            @break
        @case('chevron-down')
            <path d="m6 9.5 6 5.5 6-5.5" />
            @break
        @case('chevron-updown')
            <path d="m8 9 4-4 4 4" /><path d="m8 15 4 4 4-4" />
            @break
        @case('plus')
            <path d="M12 5.5v13M5.5 12h13" />
            @break
        @case('sun')
            <circle cx="12" cy="12" r="4" /><path d="M12 3v2M12 19v2M3 12h2M19 12h2M5.6 5.6l1.4 1.4M17 17l1.4 1.4M18.4 5.6 17 7M7 17l-1.4 1.4" />
            @break
        @case('moon')
            <path d="M20 14.5A8.5 8.5 0 1 1 9.5 4a7 7 0 0 0 10.5 10.5Z" />
            @break
        @case('logout')
            <path d="M14 4.5h4a1.5 1.5 0 0 1 1.5 1.5v12a1.5 1.5 0 0 1-1.5 1.5h-4" /><path d="M4 12h10.5" /><path d="m11 8.5 3.5 3.5-3.5 3.5" />
            @break
        @case('menu')
            <path d="M4 7h16M4 12h16M4 17h16" />
            @break
        @case('x')
            <path d="m6 6 12 12M18 6 6 18" />
            @break
        @case('clock')
            <circle cx="12" cy="12" r="8" /><path d="M12 7.5V12l3 2" />
            @break
        @case('document')
            <path d="M7 3.5h7L18.5 8v11A1.5 1.5 0 0 1 17 20.5H7A1.5 1.5 0 0 1 5.5 19V5A1.5 1.5 0 0 1 7 3.5Z" /><path d="M13.5 3.8V8.5h4.7" /><path d="M8.5 12.5h7M8.5 16h4.5" />
            @break
        @case('alert')
            <path d="M12 4 21 19.5H3L12 4Z" /><path d="M12 10v4" /><path d="M12 16.8v.4" />
            @break
        @case('banknotes')
            <rect x="3.5" y="7" width="17" height="11" rx="1.5" /><circle cx="12" cy="12.5" r="2.5" /><path d="M6.5 10v.01M17.5 15v.01" />
            @break
        @case('check-circle')
            <circle cx="12" cy="12" r="8" /><path d="m8.5 12.2 2.4 2.4 4.6-5" />
            @break
        @case('arrow-right')
            <path d="M4.5 12h15" /><path d="m14 6.5 5.5 5.5-5.5 5.5" />
            @break
        @case('grid')
            <rect x="4" y="4" width="7" height="7" rx="1.5" /><rect x="13" y="4" width="7" height="7" rx="1.5" /><rect x="4" y="13" width="7" height="7" rx="1.5" /><rect x="13" y="13" width="7" height="7" rx="1.5" />
            @break
        @case('inbox')
            <path d="M4 13.5 6 6a1.5 1.5 0 0 1 1.4-1H16.6A1.5 1.5 0 0 1 18 6l2 7.5V18a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 4 18v-4.5Z" /><path d="M4 13.5h4.5l1.2 2h4.6l1.2-2H20" />
            @break
        @case('send')
            <path d="M20 4 4 10.5l6.5 2.5L13 19.5 20 4Z" /><path d="M10.5 13 20 4" />
            @break
    @endswitch
</svg>
