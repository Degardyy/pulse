@if ($data['documents']->isEmpty())
    <p class="text-sm text-ink-3">Belum ada dokumen yang dapat Anda akses.</p>
@else
    <ul class="space-y-2.5">
        @foreach ($data['documents'] as $document)
            <li>
                <a href="{{ route('core.documents.download', $document) }}"
                   class="focusable group flex items-start gap-2.5 rounded">
                    <x-core::ui.icon name="document" class="mt-0.5 size-4 text-ink-3" />
                    <span class="min-w-0">
                        <span class="block truncate text-sm text-ink group-hover:text-accent">{{ $document->title }}</span>
                        <span class="block text-xs text-ink-3">{{ $document->visibilityLabel() }} · {{ $document->created_at->diffForHumans() }}</span>
                    </span>
                </a>
            </li>
        @endforeach
    </ul>
    <a href="{{ route('core.documents.index') }}"
       class="focusable mt-4 inline-flex items-center gap-1.5 rounded text-sm font-medium text-accent hover:text-accent-strong">
        Semua dokumen
        <x-core::ui.icon name="arrow-right" class="size-3.5" />
    </a>
@endif
