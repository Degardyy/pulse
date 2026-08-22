<?php

namespace Modules\Core\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Modules\Core\Models\Concerns\Auditable;
use Modules\Core\Services\Access\AccessService;

class User extends Authenticatable
{
    use Auditable;

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const PROVIDER_LOCAL = 'local';

    /** @var list<string> Never audited: pure noise (bumped on every login). */
    protected array $auditExclude = ['last_login_at', 'remember_token'];

    /** @var list<string> Audited as masked value only. */
    protected array $auditMask = ['password'];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'auth_provider',
        'provider_id',
        'is_active',
        'employee_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    /** @return BelongsTo<Employee, $this> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Role grants; the pivot's division_id/department_id carry the grant scope.
     *
     * @return BelongsToMany<Role, $this>
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'core_role_user')
            ->withPivot(['division_id', 'department_id']);
    }

    public function hasPermission(string $code, Division|Department|null $scope = null): bool
    {
        return app(AccessService::class)->allows($this, $code, $scope);
    }

    /** @var array{departments: list<int>, divisions: list<int>, division_leads: list<int>}|null */
    private ?array $cachedUnitIds = null;

    /**
     * The org units this user belongs to via their employee's active seats:
     * - departments     → direct department assignments;
     * - divisions       → all divisions the user is part of (incl. via department);
     * - division_leads  → divisions where the user holds a division-level seat
     *                     (used for downward visibility, e.g. documents).
     *
     * @return array{departments: list<int>, divisions: list<int>, division_leads: list<int>}
     */
    public function organizationUnitIds(): array
    {
        if ($this->cachedUnitIds !== null) {
            return $this->cachedUnitIds;
        }

        $positions = $this->employee
            ?->activeAssignments()->with('position.department')->get()
            ->pluck('position')->filter() ?? collect();

        $departments = $positions->pluck('department_id')->filter()->map(fn ($id) => (int) $id)->unique();
        $divisionLeads = $positions->pluck('division_id')->filter()->map(fn ($id) => (int) $id)->unique();
        $divisions = $divisionLeads
            ->merge($positions->pluck('department.division_id')->filter()->map(fn ($id) => (int) $id))
            ->unique();

        return $this->cachedUnitIds = [
            'departments' => $departments->values()->all(),
            'divisions' => $divisions->values()->all(),
            'division_leads' => $divisionLeads->values()->all(),
        ];
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}
