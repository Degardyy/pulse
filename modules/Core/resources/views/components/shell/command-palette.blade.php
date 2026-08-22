@props(['sections'])

@php
    $entries = [];
    foreach ($sections as $section) {
        foreach ($section['items'] as $item) {
            $entries[] = [
                'group' => $section['label'],
                'label' => $item['label'],
                'icon' => $item['icon'],
                'url' => route($item['route']),
                'keywords' => $item['keywords'],
            ];
        }
    }
@endphp

<div x-data="{
        query: '',
        selected: 0,
        entries: {{ Js::from($entries) }},
        get results() {
            const q = this.query.trim().toLowerCase();
            if (! q) return this.entries;
            return this.entries.filter(e => (e.label + ' ' + e.keywords).toLowerCase().includes(q));
        },
        open() { this.query = ''; this.selected = 0; $store.pulse.paletteOpen = true; $nextTick(() => $refs.input.focus()); },
        close() { $store.pulse.paletteOpen = false; },
        move(delta) { const n = this.results.length; if (n) this.selected = (this.selected + delta + n) % n; },
        go() { const r = this.results[this.selected]; if (r) window.location.href = r.url; },
     }"
     @keydown.window.prevent.ctrl.k="open()"
     @keydown.window.prevent.cmd.k="open()"
     x-effect="if ($store.pulse.paletteOpen) $nextTick(() => $refs.input.focus())">

    <template x-teleport="body">
        <div x-show="$store.pulse.paletteOpen" x-cloak class="fixed inset-0 z-50" role="dialog" aria-modal="true" aria-label="Pencarian global">
            <div x-show="$store.pulse.paletteOpen" x-transition.opacity.duration.150ms
                 class="absolute inset-0 bg-scrim" @click="close()"></div>

            <div x-show="$store.pulse.paletteOpen" x-transition.duration.200ms
                 class="absolute inset-x-4 top-[12vh] mx-auto max-w-xl overflow-hidden rounded-xl bg-surface shadow-2xl shadow-scrim/20 ring-1 ring-line"
                 @keydown.escape.window="close()" @keydown.down.prevent="move(1)" @keydown.up.prevent="move(-1)" @keydown.enter.prevent="go()">

                <div class="flex items-center gap-3 border-b border-line px-4">
                    <x-core::ui.icon name="search" class="size-[18px] text-ink-3" />
                    <input x-ref="input" x-model="query" @input="selected = 0" type="text"
                           placeholder="Cari halaman, modul, atau aksi…"
                           class="h-12 w-full bg-transparent text-sm text-ink placeholder-ink-3 focus:outline-none">
                    <kbd class="rounded border border-line-2 bg-surface-2 px-1.5 py-0.5 font-sans text-[10px] font-medium text-ink-3">Esc</kbd>
                </div>

                <ul class="max-h-72 overflow-y-auto p-1.5" role="listbox">
                    <template x-for="(entry, i) in results" :key="entry.url">
                        <li role="option" :aria-selected="i === selected">
                            <a :href="entry.url" @mouseenter="selected = i"
                               class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm"
                               :class="i === selected ? 'bg-accent-soft text-accent-ink' : 'text-ink-2'">
                                <span class="text-ink-3" x-html="document.querySelector('#palette-icon-' + entry.icon)?.innerHTML"></span>
                                <span class="flex-1 truncate font-medium" x-text="entry.label"></span>
                                <span class="text-xs text-ink-3" x-text="entry.group"></span>
                            </a>
                        </li>
                    </template>
                    <li x-show="results.length === 0" class="px-3 py-8 text-center text-sm text-ink-3">
                        Tidak ada hasil untuk "<span class="font-medium text-ink-2" x-text="query"></span>"
                    </li>
                </ul>

                <div class="flex items-center gap-4 border-t border-line px-4 py-2 text-[11px] text-ink-3">
                    <span><kbd class="font-sans font-medium">↑↓</kbd> navigasi</span>
                    <span><kbd class="font-sans font-medium">Enter</kbd> buka</span>
                    <span class="ml-auto">Pencarian universal hadir bertahap per modul</span>
                </div>
            </div>
        </div>
    </template>

    {{-- Hidden icon sources for palette rows --}}
    <div class="hidden" aria-hidden="true">
        @foreach (array_unique(array_column($entries, 'icon')) as $icon)
            <span id="palette-icon-{{ $icon }}"><x-core::ui.icon :name="$icon" class="size-[18px]" /></span>
        @endforeach
    </div>
</div>
