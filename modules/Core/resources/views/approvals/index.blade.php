<x-core::layouts.app :title="'Persetujuan'">
    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:py-10">
        <x-core::ui.page-header title="Persetujuan"
            description="Permintaan yang menunggu keputusan Anda, dan status permintaan Anda sendiri." />

        @if (session('status'))
            <div class="mb-4 flex items-center gap-2.5 rounded-lg bg-success-soft px-4 py-3 text-sm text-success">
                <x-core::ui.icon name="check-circle" class="size-4.5" />
                {{ session('status') }}
            </div>
        @endif

        <section aria-labelledby="pending-label" class="mb-10">
            <h2 id="pending-label" class="text-label mb-3">Menunggu keputusan saya ({{ $pending->count() }})</h2>

            @if ($pending->isEmpty())
                <x-core::ui.panel :padding="false">
                    <x-core::ui.empty-state icon="check-circle" title="Tidak ada permintaan"
                                            description="Permintaan persetujuan yang ditujukan kepada Anda akan muncul di sini." />
                </x-core::ui.panel>
            @else
                <div class="space-y-3">
                    @foreach ($pending as $instance)
                        <x-core::ui.panel class="p-5" x-data="{ act: null }">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-ink">{{ $instance->subjectLabel() }}</p>
                                    <p class="mt-0.5 text-xs text-ink-3">
                                        {{ $instance->definition->name }} · diajukan {{ $instance->requester->name }}
                                        · {{ $instance->created_at->diffForHumans() }}
                                        · langkah: {{ $instance->currentStep()?->name }}
                                    </p>
                                    @if ($instance->subject instanceof \Modules\Core\Models\Document)
                                        <a href="{{ route('core.documents.download', $instance->subject) }}"
                                           class="focusable mt-1.5 inline-flex items-center gap-1.5 rounded text-xs font-medium text-accent hover:text-accent-strong">
                                            <x-core::ui.icon name="document" class="size-3.5" />
                                            Tinjau dokumen
                                        </a>
                                    @endif
                                </div>
                                <div class="flex shrink-0 items-center gap-2">
                                    <button type="button" @click="act = act === 'approve' ? null : 'approve'"
                                            class="focusable rounded-lg bg-success-soft px-3 py-1.5 text-xs font-semibold text-success hover:opacity-80">
                                        Setujui
                                    </button>
                                    <button type="button" @click="act = act === 'reject' ? null : 'reject'"
                                            class="focusable rounded-lg bg-danger-soft px-3 py-1.5 text-xs font-semibold text-danger hover:opacity-80">
                                        Tolak
                                    </button>
                                </div>
                            </div>

                            <form x-show="act" x-cloak method="POST"
                                  :action="act === 'approve'
                                      ? '{{ route('core.approvals.approve', $instance) }}'
                                      : '{{ route('core.approvals.reject', $instance) }}'"
                                  class="mt-4 flex items-start gap-2 border-t border-line pt-4">
                                @csrf
                                <input type="text" name="note" maxlength="500"
                                       :placeholder="act === 'approve' ? 'Catatan (opsional)…' : 'Alasan penolakan (opsional)…'"
                                       class="focusable w-full rounded-lg bg-surface px-3 py-2 text-sm text-ink ring-1 ring-line-2 focus:ring-accent">
                                <button type="submit"
                                        class="focusable shrink-0 rounded-lg px-3.5 py-2 text-sm font-semibold text-white"
                                        :class="act === 'approve' ? 'bg-success' : 'bg-danger'"
                                        x-text="act === 'approve' ? 'Konfirmasi Setujui' : 'Konfirmasi Tolak'"></button>
                            </form>
                        </x-core::ui.panel>
                    @endforeach
                </div>
            @endif
        </section>

        <section aria-labelledby="requests-label">
            <h2 id="requests-label" class="text-label mb-3">Permintaan saya</h2>

            @if ($requests->isEmpty())
                <p class="text-sm text-ink-3">Belum ada permintaan yang Anda ajukan.</p>
            @else
                <div class="divide-y divide-line rounded-xl bg-surface ring-1 ring-line">
                    @foreach ($requests as $instance)
                        <div class="flex flex-wrap items-center gap-3 px-4 py-3 sm:px-5">
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-ink">{{ $instance->subjectLabel() }}</p>
                                <p class="mt-0.5 text-xs text-ink-3">
                                    {{ $instance->definition->name }} · {{ $instance->created_at->diffForHumans() }}
                                    @foreach ($instance->instanceSteps->whereNotNull('note') as $step)
                                        · "{{ $step->note }}"
                                    @endforeach
                                </p>
                            </div>
                            <x-core::ui.status :tone="match ($instance->status) {
                                'approved' => 'success', 'rejected' => 'danger', default => 'warning' }">
                                {{ ['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'][$instance->status] ?? $instance->status }}
                            </x-core::ui.status>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
</x-core::layouts.app>
