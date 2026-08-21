<?php

namespace Modules\Core\Http\Controllers\Admin;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Modules\Core\Http\Requests\Admin\StoreUserRequest;
use Modules\Core\Http\Requests\Admin\UpdateUserRequest;
use Modules\Core\Models\Employee;
use Modules\Core\Models\Role;
use Modules\Core\Models\User;
use Modules\Core\Services\UserManagementService;

class UserController
{
    use AuthorizesRequests;

    public function __construct(private readonly UserManagementService $users) {}

    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        return view('core::admin.users.index', [
            'users' => $this->users->listWithDetails(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('core::admin.users.create', $this->formOptions());
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $result = $this->users->create(
            $request->safe()->except('roles'),
            $request->validated('roles', []),
        );

        return redirect()->route('core.admin.users.index')
            ->with('status', "Akun {$result['user']->email} dibuat.")
            ->with('generated_password', $result['password']);
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        return view('core::admin.users.edit', ['user' => $user] + $this->formOptions($user));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $this->users->update(
            $user,
            $request->safe()->except('roles'),
            $request->validated('roles', []),
        );

        return redirect()->route('core.admin.users.index')
            ->with('status', "Akun {$user->email} diperbarui.");
    }

    public function resetPassword(User $user): RedirectResponse
    {
        $this->authorize('resetPassword', $user);

        $password = $this->users->resetPassword($user);

        return redirect()->route('core.admin.users.index')
            ->with('status', "Password {$user->email} di-reset.")
            ->with('generated_password', $password);
    }

    public function toggleActive(User $user): RedirectResponse
    {
        $this->authorize('toggleActive', $user);

        $this->users->toggleActive($user);

        return redirect()->route('core.admin.users.index')
            ->with('status', $user->is_active
                ? "Akun {$user->email} diaktifkan."
                : "Akun {$user->email} dinonaktifkan.");
    }

    /**
     * @return array{roles: Collection<int, Role>, employees: Collection<int, Employee>}
     */
    private function formOptions(?User $editing = null): array
    {
        return [
            'roles' => Role::orderBy('name')->get(),
            'employees' => Employee::query()
                ->where('is_active', true)
                ->whereDoesntHave('user', fn ($q) => $editing ? $q->where('id', '!=', $editing->id) : $q)
                ->orderBy('name')
                ->get(),
        ];
    }
}
