<?php

namespace Modules\Core\Services\Reporting;

use Closure;
use Modules\Core\Models\User;

/**
 * Reporting foundation: modules register exportable reports (CSV). A report
 * declares headers + a lazy row generator and, optionally, a permission code.
 */
class ReportRegistry
{
    /** @var array<string, array{title: string, description: string, permission: ?string, headers: list<string>, rows: Closure}> */
    private array $reports = [];

    /**
     * @param  list<string>  $headers
     * @param  Closure(User): iterable<int, array<int, string|int|null>>  $rows
     */
    public function register(
        string $key,
        string $title,
        string $description,
        array $headers,
        Closure $rows,
        ?string $permission = null,
    ): void {
        $this->reports[$key] = compact('title', 'description', 'permission', 'headers', 'rows');
    }

    /** @return array<string, array{title: string, description: string, permission: ?string, headers: list<string>, rows: Closure}> */
    public function availableFor(User $user): array
    {
        return array_filter(
            $this->reports,
            fn (array $report) => $report['permission'] === null || $user->hasPermission($report['permission']),
        );
    }

    /** @return array{title: string, description: string, permission: ?string, headers: list<string>, rows: Closure}|null */
    public function get(string $key): ?array
    {
        return $this->reports[$key] ?? null;
    }
}
