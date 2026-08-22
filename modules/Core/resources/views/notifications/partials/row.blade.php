@php
    /** @var \Illuminate\Notifications\DatabaseNotification $notification */
    $data = $notification->data;
    $unread = $notification->read_at === null;
    $tone = $data['tone'] ?? 'accent';
    $dotClass = ['accent' => 'bg-accent', 'success' => 'bg-success', 'warning' => 'bg-warning', 'danger' => 'bg-danger'][$tone] ?? 'bg-accent';
    $compact ??= false;
@endphp

<form method="POST" action="{{ route('core.notifications.read', $notification->id) }}">
    @csrf
    <button type="submit"
            class="focusable flex w-full items-start gap-3 px-4 py-3 text-left transition-colors duration-150 hover:bg-surface-2/70 {{ $unread ? 'bg-accent-soft/30' : '' }}">
        <span class="mt-1.5 size-2 shrink-0 rounded-full {{ $unread ? $dotClass : 'bg-line-2' }}"></span>
        <span class="min-w-0 flex-1">
            <span class="block text-sm {{ $unread ? 'font-semibold text-ink' : 'font-medium text-ink-2' }}">
                {{ $data['title'] ?? '—' }}
            </span>
            @if (! empty($data['body']))
                <span class="mt-0.5 block text-xs leading-relaxed text-ink-3 {{ $compact ? 'line-clamp-2' : '' }}">{{ $data['body'] }}</span>
            @endif
            <span class="mt-1 block text-[11px] text-ink-3">{{ $notification->created_at->diffForHumans() }}</span>
        </span>
    </button>
</form>
