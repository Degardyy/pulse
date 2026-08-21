<?php

namespace Modules\Core\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\Core\Models\Directorate;

/**
 * Read model for the organization structure. Controllers — and later the AI
 * gateway and other modules — consume the structure through this service, never
 * by querying the core_* tables directly (ADR-005, module boundary rules).
 */
class OrganizationService
{
    /**
     * The active structure as an ordered tree: directorates → divisions → departments.
     *
     * @return Collection<int, Directorate>
     */
    public function structureTree(): Collection
    {
        return Directorate::query()
            ->where('is_active', true)
            ->with([
                'positions' => fn ($q) => $q->where('is_active', true),
                'positions.currentAssignment.employee',
                'divisions' => fn ($q) => $q->where('is_active', true),
                'divisions.positions' => fn ($q) => $q->where('is_active', true),
                'divisions.positions.currentAssignment.employee',
                'divisions.departments' => fn ($q) => $q->where('is_active', true),
                'divisions.departments.positions' => fn ($q) => $q->where('is_active', true),
                'divisions.departments.positions.currentAssignment.employee',
            ])
            ->orderBy('sort_order')
            ->get();
    }

    /** @return array{directorates: int, divisions: int, departments: int} */
    public function counts(): array
    {
        $tree = $this->structureTree();

        return [
            'directorates' => $tree->count(),
            'divisions' => $tree->sum(fn (Directorate $d) => $d->divisions->count()),
            'departments' => $tree->sum(fn (Directorate $d) => $d->divisions->sum(fn ($div) => $div->departments->count())),
        ];
    }
}
