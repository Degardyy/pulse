<?php

namespace Modules\Core\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\Core\Models\Employee;
use Modules\Core\Models\Position;

/**
 * Read model for employees and structural seats. Other modules and the AI
 * gateway consume this service, never the core_* tables directly (ADR-005).
 */
class EmployeeService
{
    /**
     * Active employees with the seats they currently hold, ordered by name.
     *
     * @return Collection<int, Employee>
     */
    public function listWithPositions(): Collection
    {
        return Employee::query()
            ->where('is_active', true)
            ->with('activeAssignments.position')
            ->orderBy('name')
            ->get();
    }

    /** @return array{employees: int, positions: int, vacant: int, acting: int} */
    public function counts(): array
    {
        $positions = Position::query()
            ->where('is_active', true)
            ->with('currentAssignment')
            ->get();

        return [
            'employees' => Employee::where('is_active', true)->count(),
            'positions' => $positions->count(),
            'vacant' => $positions->filter(fn (Position $p) => $p->isVacant())->count(),
            'acting' => $positions->filter(fn (Position $p) => $p->currentAssignment?->is_acting === true)->count(),
        ];
    }
}
