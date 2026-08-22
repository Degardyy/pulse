<?php

namespace Modules\Core\Services\Workflow;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Modules\Core\Events\WorkflowApproved;
use Modules\Core\Events\WorkflowRejected;
use Modules\Core\Models\Position;
use Modules\Core\Models\Role;
use Modules\Core\Models\User;
use Modules\Core\Models\WorkflowDefinition;
use Modules\Core\Models\WorkflowInstance;
use Modules\Core\Models\WorkflowInstanceStep;
use Modules\Core\Models\WorkflowStep;
use Modules\Core\Services\Notifier;

/**
 * Configuration-driven sequential approval engine (ADR-009).
 *
 * Approvers are frozen per instance at start time (position or role id), so
 * pending work stays queryable and later org changes never corrupt in-flight
 * approvals. Modules react to outcomes via WorkflowApproved/WorkflowRejected
 * events — the engine knows nothing about documents, budgets, or tickets.
 */
class WorkflowService
{
    public function __construct(private readonly Notifier $notifier) {}

    public function start(string $definitionCode, Model $subject, User $requester): WorkflowInstance
    {
        $definition = WorkflowDefinition::where('code', $definitionCode)
            ->where('is_active', true)
            ->with('steps')
            ->firstOrFail();

        if ($definition->steps->isEmpty()) {
            throw new InvalidArgumentException("Workflow {$definitionCode} tidak memiliki langkah.");
        }

        $instance = WorkflowInstance::create([
            'definition_id' => $definition->id,
            'subject_type' => $subject->getMorphClass(),
            'subject_id' => $subject->getKey(),
            'requested_by' => $requester->id,
            'status' => WorkflowInstance::STATUS_PENDING,
        ]);

        foreach ($definition->steps as $step) {
            [$positionId, $roleId] = $this->resolveApprover($step, $requester);

            $instance->instanceSteps()->create([
                'sort_order' => $step->sort_order,
                'name' => $step->name,
                'position_id' => $positionId,
                'role_id' => $roleId,
                'status' => WorkflowInstanceStep::STATUS_PENDING,
            ]);
        }

        $this->notifyCurrentApprovers($instance->fresh('instanceSteps'));

        return $instance;
    }

    public function approve(WorkflowInstance $instance, User $user, ?string $note = null): void
    {
        $step = $this->assertActionable($instance, $user);

        $step->update([
            'status' => WorkflowInstanceStep::STATUS_APPROVED,
            'acted_by' => $user->id,
            'acted_at' => now(),
            'note' => $note,
        ]);

        $instance->unsetRelation('instanceSteps');

        if ($instance->currentStep() !== null) {
            $this->notifyCurrentApprovers($instance);

            return;
        }

        $instance->update(['status' => WorkflowInstance::STATUS_APPROVED]);

        $this->notifier->send(
            $instance->requester,
            'Permintaan Anda disetujui',
            "{$instance->definition->name}: \"{$instance->subjectLabel()}\"",
            route('core.approvals.index'),
            'success',
        );

        event(new WorkflowApproved($instance));
    }

    public function reject(WorkflowInstance $instance, User $user, ?string $note = null): void
    {
        $step = $this->assertActionable($instance, $user);

        $step->update([
            'status' => WorkflowInstanceStep::STATUS_REJECTED,
            'acted_by' => $user->id,
            'acted_at' => now(),
            'note' => $note,
        ]);

        $instance->update(['status' => WorkflowInstance::STATUS_REJECTED]);

        $this->notifier->send(
            $instance->requester,
            'Permintaan Anda ditolak',
            "{$instance->definition->name}: \"{$instance->subjectLabel()}\"".($note ? " — {$note}" : ''),
            route('core.approvals.index'),
            'danger',
        );

        event(new WorkflowRejected($instance, $note));
    }

    /**
     * Instances waiting on a decision this user is eligible to make.
     *
     * @return Collection<int, WorkflowInstance>
     */
    public function pendingFor(User $user): Collection
    {
        [$positionIds, $roleIds] = $this->eligibilityOf($user);

        return WorkflowInstance::query()
            ->where('status', WorkflowInstance::STATUS_PENDING)
            ->with(['definition', 'requester', 'subject', 'instanceSteps'])
            ->latest()
            ->get()
            ->filter(function (WorkflowInstance $instance) use ($positionIds, $roleIds) {
                $step = $instance->currentStep();

                return $step !== null && $this->stepMatches($step, $positionIds, $roleIds);
            })
            ->values();
    }

    /** @return Collection<int, WorkflowInstance> */
    public function requestsOf(User $user): Collection
    {
        return WorkflowInstance::query()
            ->where('requested_by', $user->id)
            ->with(['definition', 'subject', 'instanceSteps'])
            ->latest()
            ->limit(50)
            ->get();
    }

    /** Whether the user may decide the subject's pending workflow right now. */
    public function isApproverFor(User $user, Model $subject): bool
    {
        $instance = WorkflowInstance::query()
            ->where('subject_type', $subject->getMorphClass())
            ->where('subject_id', $subject->getKey())
            ->where('status', WorkflowInstance::STATUS_PENDING)
            ->with('instanceSteps')
            ->latest()
            ->first();

        if ($instance === null || ($step = $instance->currentStep()) === null) {
            return false;
        }

        [$positionIds, $roleIds] = $this->eligibilityOf($user);

        return $this->stepMatches($step, $positionIds, $roleIds);
    }

    private function assertActionable(WorkflowInstance $instance, User $user): WorkflowInstanceStep
    {
        $step = $instance->currentStep();

        if ($instance->status !== WorkflowInstance::STATUS_PENDING || $step === null) {
            throw new AuthorizationException('Permintaan ini sudah diputuskan.');
        }

        [$positionIds, $roleIds] = $this->eligibilityOf($user);

        if (! $this->stepMatches($step, $positionIds, $roleIds)) {
            throw new AuthorizationException('Anda bukan approver langkah ini.');
        }

        return $step;
    }

    /** @return array{0: list<int>, 1: list<int>} position ids, global role ids */
    private function eligibilityOf(User $user): array
    {
        $positionIds = $user->employee
            ?->activeAssignments()->pluck('position_id')->map(fn ($id) => (int) $id)->all() ?? [];

        $roleIds = $user->roles()
            ->wherePivotNull('division_id')->wherePivotNull('department_id')
            ->pluck('core_roles.id')->map(fn ($id) => (int) $id)->all();

        return [$positionIds, $roleIds];
    }

    private function stepMatches(WorkflowInstanceStep $step, array $positionIds, array $roleIds): bool
    {
        return ($step->position_id !== null && in_array((int) $step->position_id, $positionIds, true))
            || ($step->role_id !== null && in_array((int) $step->role_id, $roleIds, true));
    }

    /** @return array{0: ?int, 1: ?int} [position_id, role_id] */
    private function resolveApprover(WorkflowStep $step, User $requester): array
    {
        $units = $requester->organizationUnitIds();

        return match ($step->approver_type) {
            WorkflowStep::APPROVER_POSITION => [
                Position::where('code', $step->approver_value)->firstOrFail()->id, null,
            ],
            WorkflowStep::APPROVER_DEPARTMENT_HEAD => [
                Position::where('level', Position::LEVEL_DEPARTMENT_HEAD)
                    ->whereIn('department_id', $units['departments'] ?: [-1])
                    ->firstOrFail()->id, null,
            ],
            WorkflowStep::APPROVER_DIVISION_HEAD => [
                Position::where('level', Position::LEVEL_DIVISION_HEAD)
                    ->whereIn('division_id', $units['divisions'] ?: [-1])
                    ->firstOrFail()->id, null,
            ],
            WorkflowStep::APPROVER_ROLE => [
                null, Role::where('code', $step->approver_value)->firstOrFail()->id,
            ],
            default => throw new InvalidArgumentException("Jenis approver tidak dikenal: {$step->approver_type}"),
        };
    }

    private function notifyCurrentApprovers(WorkflowInstance $instance): void
    {
        $step = $instance->currentStep();

        if ($step === null) {
            return;
        }

        $approvers = User::query()
            ->where('is_active', true)
            ->where(function ($q) use ($step) {
                if ($step->position_id !== null) {
                    $q->orWhereHas('employee.activeAssignments', fn ($q) => $q->where('position_id', $step->position_id));
                }
                if ($step->role_id !== null) {
                    $q->orWhereHas('roles', fn ($q) => $q->where('core_roles.id', $step->role_id)
                        ->whereNull('core_role_user.division_id')->whereNull('core_role_user.department_id'));
                }
            })
            ->get();

        if ($approvers->isNotEmpty()) {
            $this->notifier->send(
                $approvers,
                'Persetujuan menunggu Anda',
                "{$instance->definition->name}: \"{$instance->subjectLabel()}\" — diajukan {$instance->requester->name}",
                route('core.approvals.index'),
            );
        }
    }
}
