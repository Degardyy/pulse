<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Models\Concerns\Auditable;

class Directorate extends Model
{
    use Auditable;

    protected $table = 'core_directorates';

    protected $fillable = ['code', 'name', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /** @return HasMany<Division, $this> */
    public function divisions(): HasMany
    {
        return $this->hasMany(Division::class)->orderBy('sort_order');
    }

    /** @return HasMany<Position, $this> */
    public function positions(): HasMany
    {
        return $this->hasMany(Position::class)->orderBy('sort_order');
    }
}
