<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PositionAssignment extends Model
{
    protected $table = 'core_position_assignments';

    protected $fillable = ['position_id', 'employee_id', 'is_acting', 'started_at', 'ended_at'];

    protected function casts(): array
    {
        return [
            'is_acting' => 'boolean',
            'started_at' => 'date',
            'ended_at' => 'date',
        ];
    }

    /** @return BelongsTo<Position, $this> */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    /** @return BelongsTo<Employee, $this> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
