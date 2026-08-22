<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Models\Concerns\Auditable;

class Document extends Model
{
    use Auditable;

    public const VISIBILITY_PALJAYA = 'paljaya';

    public const VISIBILITY_DIVISION = 'division';

    public const VISIBILITY_DEPARTMENT = 'department';

    protected $table = 'core_documents';

    protected $fillable = [
        'title', 'description', 'category', 'file_path', 'file_name', 'mime_type',
        'size', 'visibility', 'division_id', 'department_id', 'uploaded_by',
    ];

    /** @return BelongsTo<User, $this> */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
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

    /**
     * Visibility rules (product-owner decision, 2026-08-23):
     * - paljaya    → every authenticated user;
     * - division   → every member of that division, incl. its departments;
     * - department → members of that department, plus holders of a
     *                division-level seat above it (leadership reads down).
     * Uploaders always see their own documents; core.documents.manage sees all.
     *
     * @param  Builder<Document>  $query
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->hasPermission('core.documents.manage')) {
            return $query;
        }

        $units = $user->organizationUnitIds();
        $ledDepartmentIds = $units['division_leads'] === []
            ? []
            : Department::whereIn('division_id', $units['division_leads'])->pluck('id')->all();

        return $query->where(function (Builder $q) use ($units, $ledDepartmentIds, $user) {
            $q->where('visibility', self::VISIBILITY_PALJAYA)
                ->orWhere(fn (Builder $q) => $q->where('visibility', self::VISIBILITY_DIVISION)
                    ->whereIn('division_id', $units['divisions'] ?: [-1]))
                ->orWhere(fn (Builder $q) => $q->where('visibility', self::VISIBILITY_DEPARTMENT)
                    ->whereIn('department_id', array_merge($units['departments'], $ledDepartmentIds) ?: [-1]))
                ->orWhere('uploaded_by', $user->id);
        });
    }

    public function visibilityLabel(): string
    {
        return match ($this->visibility) {
            self::VISIBILITY_PALJAYA => 'Seluruh Paljaya',
            self::VISIBILITY_DIVISION => $this->division?->name ?? 'Division',
            self::VISIBILITY_DEPARTMENT => $this->department?->name ?? 'Department',
            default => $this->visibility,
        };
    }

    public function sizeLabel(): string
    {
        return match (true) {
            $this->size >= 1_048_576 => number_format($this->size / 1_048_576, 1).' MB',
            $this->size >= 1_024 => number_format($this->size / 1_024).' KB',
            default => $this->size.' B',
        };
    }
}
