<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Models\Concerns\Auditable;

class Division extends Model
{
    use Auditable;

    public const TYPE_DIVISION = 'division';

    /** A unit (e.g. Internal Audit) reports to a director without departments. */
    public const TYPE_UNIT = 'unit';

    protected $table = 'core_divisions';

    protected $fillable = ['directorate_id', 'code', 'name', 'type', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /** @return BelongsTo<Directorate, $this> */
    public function directorate(): BelongsTo
    {
        return $this->belongsTo(Directorate::class);
    }

    /** @return HasMany<Department, $this> */
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class)->orderBy('sort_order');
    }

    /** @return HasMany<Position, $this> */
    public function positions(): HasMany
    {
        return $this->hasMany(Position::class)->orderBy('sort_order');
    }
}
