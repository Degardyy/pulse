<?php

namespace Modules\Core\Services\Dashboard;

use Closure;
use Modules\Core\Models\User;

/**
 * Dashboard foundation: modules register Home widgets here (Division Portals
 * will reuse the same registry for their own dashboards). Data closures are
 * lazy — nothing runs until the widget is actually rendered for a user.
 */
class WidgetRegistry
{
    /** @var array<string, array{title: string, view: string, sort: int, visible: ?Closure, data: ?Closure}> */
    private array $widgets = [];

    public function register(
        string $key,
        string $title,
        string $view,
        int $sort = 100,
        ?Closure $visible = null,
        ?Closure $data = null,
    ): void {
        $this->widgets[$key] = compact('title', 'view', 'sort', 'visible', 'data');
    }

    /**
     * @return list<array{key: string, title: string, view: string, data: array<string, mixed>}>
     */
    public function forUser(User $user): array
    {
        $resolved = [];

        foreach ($this->widgets as $key => $widget) {
            if ($widget['visible'] !== null && ! ($widget['visible'])($user)) {
                continue;
            }

            $resolved[] = [
                'key' => $key,
                'title' => $widget['title'],
                'view' => $widget['view'],
                'sort' => $widget['sort'],
                'data' => $widget['data'] !== null ? ($widget['data'])($user) : [],
            ];
        }

        usort($resolved, fn ($a, $b) => $a['sort'] <=> $b['sort']);

        return $resolved;
    }
}
