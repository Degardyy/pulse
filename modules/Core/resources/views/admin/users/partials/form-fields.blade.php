@php /** @var \Modules\Core\Models\User|null $user */ $user ??= null; @endphp

<div>
    <label for="name" class="mb-1.5 block text-sm font-medium text-ink">Nama</label>
    <input id="name" name="name" type="text" required value="{{ old('name', $user?->name) }}"
           class="focusable w-full rounded-lg bg-surface px-3.5 py-2.5 text-sm text-ink ring-1 ring-line-2 focus:ring-accent">
    @error('name') <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
</div>

<div>
    <label for="email" class="mb-1.5 block text-sm font-medium text-ink">Email</label>
    <input id="email" name="email" type="email" required value="{{ old('email', $user?->email) }}"
           class="focusable w-full rounded-lg bg-surface px-3.5 py-2.5 text-sm text-ink ring-1 ring-line-2 focus:ring-accent">
    @error('email') <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
</div>

<div>
    <label for="employee_id" class="mb-1.5 block text-sm font-medium text-ink">
        Pegawai <span class="font-normal text-ink-3">(opsional — tautkan ke data pegawai)</span>
    </label>
    <select id="employee_id" name="employee_id"
            class="focusable w-full rounded-lg bg-surface px-3.5 py-2.5 text-sm text-ink ring-1 ring-line-2 focus:ring-accent">
        <option value="">— Tidak tertaut —</option>
        @foreach ($employees as $employee)
            <option value="{{ $employee->id }}" @selected((int) old('employee_id', $user?->employee_id) === $employee->id)>
                {{ $employee->name }}
            </option>
        @endforeach
    </select>
    @error('employee_id') <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
</div>

<fieldset>
    <legend class="mb-1.5 text-sm font-medium text-ink">Role</legend>
    <div class="space-y-2 rounded-lg bg-surface-2/50 p-4 ring-1 ring-line">
        @foreach ($roles as $role)
            <label class="flex items-start gap-3 text-sm">
                <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                       @checked(in_array($role->id, old('roles', $user?->roles->pluck('id')->all() ?? [])))
                       class="mt-0.5 size-4 rounded border-line-2 text-accent focus:ring-accent">
                <span>
                    <span class="font-medium text-ink">{{ $role->name }}</span>
                    @if ($role->description)
                        <span class="block text-xs text-ink-3">{{ $role->description }}</span>
                    @endif
                </span>
            </label>
        @endforeach
    </div>
    @error('roles') <p class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
</fieldset>
