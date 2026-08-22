<?php

namespace Modules\Core\Notifications;

use Illuminate\Notifications\Notification;

/**
 * The single in-app notification shape (ADR-002 KISS): title, optional body,
 * optional action URL, semantic tone. Additional channels (mail, WhatsApp)
 * can be added here later without touching callers.
 */
class PulseNotification extends Notification
{
    public function __construct(
        public readonly string $title,
        public readonly ?string $body = null,
        public readonly ?string $url = null,
        public readonly string $tone = 'accent',
    ) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array{title: string, body: ?string, url: ?string, tone: string} */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'url' => $this->url,
            'tone' => $this->tone,
        ];
    }
}
