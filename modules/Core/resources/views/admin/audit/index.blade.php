<x-core::layouts.app :title="'Audit Trail'">
    <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:py-10">
        <x-core::ui.page-header title="Audit Trail"
            description="Jejak perubahan data dan aktivitas autentikasi — append-only, tidak dapat diubah." />

        {{-- Event filter --}}
        <div class="mb-5 flex flex-wrap items-center gap-1.5">
            <a href="{{ route('core.admin.audit.index') }}"
               @class([
                   'focusable rounded-full px-3 py-1.5 text-xs font-medium transition-colors',
                   'bg-accent-soft text-accent-ink' => ! $event,
                   'text-ink-2 hover:bg-surface-2' => $event,
               ])>Semua</a>
            @foreach ($events as $code => $label)
                <a href="{{ route('core.admin.audit.index', ['event' => $code]) }}"
                   @class([
                       'focusable rounded-full px-3 py-1.5 text-xs font-medium transition-colors',
                       'bg-accent-soft text-accent-ink' => $event === $code,
                       'text-ink-2 hover:bg-surface-2' => $event !== $code,
                   ])>{{ $label }}</a>
            @endforeach
        </div>

        @if ($logs->isEmpty())
            <x-core::ui.panel :padding="false">
                <x-core::ui.empty-state icon="clock" title="Belum ada jejak audit"
                                        description="Perubahan data dan aktivitas login akan tercatat di sini secara otomatis." />
            </x-core::ui.panel>
        @else
            <div class="divide-y divide-line rounded-xl bg-surface ring-1 ring-line">
                @foreach ($logs as $log)
                    @php
                        $tone = match ($log->event) {
                            \Modules\Core\Models\AuditLog::EVENT_CREATED => 'success',
                            \Modules\Core\Models\AuditLog::EVENT_DELETED => 'danger',
                            \Modules\Core\Models\AuditLog::EVENT_UPDATED,
                            \Modules\Core\Models\AuditLog::EVENT_ROLES_SYNCED => 'accent',
                            default => 'neutral',
                        };
                        $hasDiff = $log->old_values || $log->new_values;
                    @endphp
                    <div x-data="{ open: false }">
                        <button type="button" @click="open = !open" @disabled(! $hasDiff)
                                class="focusable flex w-full flex-wrap items-center gap-x-4 gap-y-1.5 px-4 py-3 text-left transition-colors duration-150 hover:bg-surface-2/60 sm:px-5">
                            <span class="w-32 shrink-0 text-xs tabular-nums text-ink-3">
                                {{ $log->created_at->format('d M Y H:i:s') }}
                            </span>
                            <span class="flex w-40 shrink-0 items-center gap-2 text-sm text-ink">
                                @if ($log->user)
                                    <x-core::ui.avatar :name="$log->user->name" size="sm" />
                                    <span class="truncate">{{ $log->user->name }}</span>
                                @else
                                    <span class="text-ink-3">System</span>
                                @endif
                            </span>
                            <x-core::ui.status :tone="$tone">{{ $events[$log->event] ?? $log->event }}</x-core::ui.status>
                            <span class="min-w-0 flex-1 truncate text-sm text-ink-2">{{ $log->subjectLabel() }}</span>
                            @if ($hasDiff)
                                <x-core::ui.icon name="chevron-down" class="size-4 text-ink-3 transition-transform duration-200" ::class="open && 'rotate-180'" />
                            @endif
                        </button>

                        @if ($hasDiff)
                            <div x-show="open" x-cloak x-transition.opacity.duration.150ms class="border-t border-line bg-surface-2/40 px-4 py-4 sm:px-5">
                                <div class="grid gap-6 text-xs sm:grid-cols-2">
                                    @if ($log->old_values)
                                        <div>
                                            <p class="text-label mb-2">Sebelum</p>
                                            <dl class="space-y-1.5">
                                                @foreach ($log->old_values as $key => $value)
                                                    <div class="flex gap-2">
                                                        <dt class="w-32 shrink-0 font-medium text-ink-3">{{ $key }}</dt>
                                                        <dd class="break-all font-mono text-ink-2">{{ is_scalar($value) || $value === null ? ($value ?? 'null') : json_encode($value, JSON_UNESCAPED_UNICODE) }}</dd>
                                                    </div>
                                                @endforeach
                                            </dl>
                                        </div>
                                    @endif
                                    @if ($log->new_values)
                                        <div>
                                            <p class="text-label mb-2">Sesudah</p>
                                            <dl class="space-y-1.5">
                                                @foreach ($log->new_values as $key => $value)
                                                    <div class="flex gap-2">
                                                        <dt class="w-32 shrink-0 font-medium text-ink-3">{{ $key }}</dt>
                                                        <dd class="break-all font-mono text-ink">{{ is_scalar($value) || $value === null ? ($value ?? 'null') : json_encode($value, JSON_UNESCAPED_UNICODE) }}</dd>
                                                    </div>
                                                @endforeach
                                            </dl>
                                        </div>
                                    @endif
                                </div>
                                @if ($log->ip_address)
                                    <p class="mt-4 text-[11px] text-ink-3">IP {{ $log->ip_address }}@if ($log->user_agent) · {{ Str::limit($log->user_agent, 90) }}@endif</p>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-5 flex items-center justify-between text-sm">
                <p class="text-ink-3">
                    {{ $logs->firstItem() }}–{{ $logs->lastItem() }} dari {{ number_format($logs->total()) }} entri
                </p>
                <div class="flex items-center gap-2">
                    @if ($logs->onFirstPage())
                        <span class="rounded-lg px-3 py-1.5 text-ink-3/50">Sebelumnya</span>
                    @else
                        <a href="{{ $logs->previousPageUrl() }}" class="focusable rounded-lg px-3 py-1.5 font-medium text-ink-2 hover:bg-surface-2">Sebelumnya</a>
                    @endif
                    @if ($logs->hasMorePages())
                        <a href="{{ $logs->nextPageUrl() }}" class="focusable rounded-lg px-3 py-1.5 font-medium text-ink-2 hover:bg-surface-2">Berikutnya</a>
                    @else
                        <span class="rounded-lg px-3 py-1.5 text-ink-3/50">Berikutnya</span>
                    @endif
                </div>
            </div>
        @endif
    </div>
</x-core::layouts.app>
