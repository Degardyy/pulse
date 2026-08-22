<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\Core\Models\Concerns\Auditable;

class Position extends Model
{
    use Auditable;

    public const LEVEL_PRESIDENT_DIRECTOR = 'president_director';

    public const LEVEL_DIRECTOR = 'director';

    public const LEVEL_DIVISION_HEAD = 'division_head';

    public const LEVEL_DEPARTMENT_HEAD = 'department_head';

    public const LEVEL_UNIT_HEAD = 'unit_head';

    public const LEVEL_UNIT_SECRETARY = 'unit_secretary';

    public const LEVEL_STAFF = 'staff';

    protected $table = 'core_positions';

    protected $fillable = [
        'code', 'name', 'level', 'directorate_id', 'division_id', 'department_id', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /** @return BelongsTo<Directorate, $this> */
    public function directorate(): BelongsTo
    {
        return $this->belongsTo(Directorate::class);
    }

    /** @return BelongsTo<Division, $this> */
    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    /** @return BelongsTo<Department, $this> */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /** @return HasMany<PositionAssignment, $this> */
    public function assignments(): HasMany
    {
        return $this->hasMany(PositionAssignment::class);
    }

    /**
     * The assignment currently occupying this seat (null means vacant).
     *
     * @return HasOne<PositionAssignment, $this>
     */
    public function currentAssignment(): HasOne
    {
        return $this->hasOne(PositionAssignment::class)->whereNull('ended_at');
    }

    public function isVacant(): bool
    {
        return $this->currentAssignment === null;
    }
}
