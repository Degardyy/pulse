<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Employee extends Model
{
    protected $table = 'core_employees';

    protected $fillable = ['name', 'employee_number', 'email', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /** @return HasMany<PositionAssignment, $this> */
    public function assignments(): HasMany
    {
        return $this->hasMany(PositionAssignment::class);
    }

    /** @return HasMany<PositionAssignment, $this> */
    public function activeAssignments(): HasMany
    {
        return $this->hasMany(PositionAssignment::class)->whereNull('ended_at');
    }

    /** @return HasOne<User, $this> */
    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }
}
