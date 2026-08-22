<x-core::layouts.app :title="'Dokumen'">
    <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:py-10">
        <x-core::ui.page-header title="Dokumen"
            description="Dokumen yang dapat Anda akses — sesuai unit kerja dan lingkup publikasinya.">
            <x-slot:actions>
                @if ($canCreate)
                    <a href="{{ route('core.documents.create') }}"
                       class="focusable inline-flex items-center gap-1.5 rounded-lg bg-accent px-3.5 py-2 text-sm font-semibold text-white transition-colors duration-150 hover:bg-accent-strong">
                        <x-core::ui.icon name="plus" class="size-4" />
                        Unggah dokumen
                    </a>
                @endif
            </x-slot:actions>
        </x-core::ui.page-header>

        @if (session('status'))
            <div class="mb-4 flex items-center gap-2.5 rounded-lg bg-success-soft px-4 py-3 text-sm text-success">
                <x-core::ui.icon name="check-circle" class="size-4.5" />
                {{ session('status') }}
            </div>
        @endif

        {{-- Scope filter --}}
        <div class="mb-5 flex flex-wrap items-center gap-1.5">
            @foreach ([null => 'Semua', 'unit' => 'Unit saya', 'paljaya' => 'Seluruh Paljaya'] as $value => $label)
                <a href="{{ route('core.documents.index', $value ? ['lingkup' => $value] : []) }}"
                   @class([
                       'focusable rounded-full px-3 py-1.5 text-xs font-medium transition-colors',
                       'bg-accent-soft text-accent-ink' => $filter === $value,
                       'text-ink-2 hover:bg-surface-2' => $filter !== $value,
                   ])>{{ $label }}</a>
            @endforeach
        </div>

        @if ($documents->isEmpty())
            <x-core::ui.panel :padding="false">
                <x-core::ui.empty-state icon="document" title="Belum ada dokumen"
                                        description="Dokumen yang dibagikan ke unit Anda atau ke seluruh Paljaya akan muncul di sini." />
            </x-core::ui.panel>
        @else
            <div class="divide-y divide-line rounded-xl bg-surface ring-1 ring-line">
                @foreach ($documents as $document)
                    <div class="flex items-center gap-4 px-4 py-3.5 transition-colors duration-150 hover:bg-surface-2/60 sm:px-5">
                        <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-surface-2 text-ink-3">
                            <x-core::ui.icon name="document" class="size-4.5" />
                        </span>
                        <div class="min-w-0 flex-1">
                            <a href="{{ route('core.documents.download', $document) }}"
                               class="focusable rounded text-sm font-medium text-ink hover:text-accent">
                                {{ $document->title }}
                            </a>
                            <p class="mt-0.5 flex flex-wrap items-center gap-x-2 text-xs text-ink-3">
                                <span @class([
                                    'font-medium',
                                    'text-accent' => $document->visibility === \Modules\Core\Models\Document::VISIBILITY_PALJAYA,
                                    'text-ink-2' => $document->visibility !== \Modules\Core\Models\Document::VISIBILITY_PALJAYA,
                                ])>{{ $document->visibilityLabel() }}</span>
                                @if ($document->category) <span>·</span><span>{{ $document->category }}</span> @endif
                                <span>·</span><span>{{ $document->sizeLabel() }}</span>
                                <span>·</span><span>{{ $document->uploader->name }}</span>
                                <span>·</span><span>{{ $document->created_at->diffForHumans() }}</span>
                            </p>
                        </div>
                        <div class="flex shrink-0 items-center gap-1">
                            <a href="{{ route('core.documents.download', $document) }}"
                               class="focusable rounded-lg px-2.5 py-1.5 text-xs font-semibold text-accent hover:bg-accent-soft">Unduh</a>
                            @can('delete', $document)
                                <form method="POST" action="{{ route('core.documents.destroy', $document) }}"
                                      onsubmit="return confirm('Hapus dokumen “{{ $document->title }}”?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="focusable rounded-lg px-2.5 py-1.5 text-xs font-semibold text-danger hover:bg-danger-soft">Hapus</button>
                                </form>
                            @endcan
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-5 flex items-center justify-between text-sm">
                <p class="text-ink-3">{{ $documents->firstItem() }}–{{ $documents->lastItem() }} dari {{ $documents->total() }}</p>
                <div class="flex items-center gap-2">
                    @if (! $documents->onFirstPage())
                        <a href="{{ $documents->previousPageUrl() }}" class="focusable rounded-lg px-3 py-1.5 font-medium text-ink-2 hover:bg-surface-2">Sebelumnya</a>
                    @endif
                    @if ($documents->hasMorePages())
                        <a href="{{ $documents->nextPageUrl() }}" class="focusable rounded-lg px-3 py-1.5 font-medium text-ink-2 hover:bg-surface-2">Berikutnya</a>
                    @endif
                </div>
            </div>
        @endif
    </div>
</x-core::layouts.app>
