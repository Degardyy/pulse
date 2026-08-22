<?php

namespace Modules\Core\Services;

use Illuminate\Support\Facades\Notification;
use Modules\Core\Models\User;
use Modules\Core\Notifications\PulseNotification;

/**
 * The one door modules use to notify people (service layer — HTTP, jobs, and
 * later the AI gateway all pass through here, ADR-005).
 */
class Notifier
{
    /**
     * @param  User|iterable<int, User>  $users
     */
    public function send(User|iterable $users, string $title, ?string $body = null, ?string $url = null, string $tone = 'accent'): void
    {
        Notification::send($users, new PulseNotification($title, $body, $url, $tone));
    }
}
