<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    public const CODE_ADMINISTRATOR = 'administrator';

    public const CODE_USER_ADMINISTRATOR = 'user-administrator';

    protected $table = 'core_roles';

    protected $fillable = ['code', 'name', 'description', 'is_system', 'is_super'];

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
            'is_super' => 'boolean',
        ];
    }

    /** @return BelongsToMany<Permission, $this> */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'core_permission_role');
    }
}
