@php /** @var \Modules\Core\Models\User|null $user */ $user ??= null; @endphp

<div>
    <label for="name" class="mb-1.5 block text-sm font-medium text-slate-700">Nama</label>
    <input id="name" name="name" type="text" required value="{{ old('name', $user?->name) }}"
           class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-paljaya-500 focus:outline-none focus:ring-2 focus:ring-paljaya-200">
    @error('name') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div>
    <label for="email" class="mb-1.5 block text-sm font-medium text-slate-700">Email</label>
    <input id="email" name="email" type="email" required value="{{ old('email', $user?->email) }}"
           class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-paljaya-500 focus:outline-none focus:ring-2 focus:ring-paljaya-200">
    @error('email') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div>
    <label for="employee_id" class="mb-1.5 block text-sm font-medium text-slate-700">
        Pegawai <span class="font-normal text-slate-400">(opsional — tautkan ke data pegawai)</span>
    </label>
    <select id="employee_id" name="employee_id"
            class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-paljaya-500 focus:outline-none focus:ring-2 focus:ring-paljaya-200">
        <option value="">— Tidak tertaut —</option>
        @foreach ($employees as $employee)
            <option value="{{ $employee->id }}" @selected((int) old('employee_id', $user?->employee_id) === $employee->id)>
                {{ $employee->name }}
            </option>
        @endforeach
    </select>
    @error('employee_id') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<fieldset>
    <legend class="mb-1.5 text-sm font-medium text-slate-700">Role</legend>
    <div class="space-y-2 rounded-lg border border-slate-200 p-4">
        @foreach ($roles as $role)
            <label class="flex items-start gap-3 text-sm">
                <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                       @checked(in_array($role->id, old('roles', $user?->roles->pluck('id')->all() ?? [])))
                       class="mt-0.5 size-4 rounded border-slate-300 text-paljaya-500 focus:ring-paljaya-200">
                <span>
                    <span class="font-medium text-slate-800">{{ $role->name }}</span>
                    @if ($role->description)
                        <span class="block text-xs text-slate-500">{{ $role->description }}</span>
                    @endif
                </span>
            </label>
        @endforeach
    </div>
    @error('roles') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
</fieldset>
