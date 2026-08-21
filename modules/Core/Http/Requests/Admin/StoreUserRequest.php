<?php

namespace Modules\Core\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Core\Models\User;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Before validation, so unauthorized callers get 403 — not field errors.
        return $this->user()->can('create', User::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'employee_id' => [
                'nullable', 'integer',
                Rule::exists('core_employees', 'id'),
                Rule::unique('users', 'employee_id'),
            ],
            'roles' => ['array'],
            'roles.*' => ['integer', Rule::exists('core_roles', 'id')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'Email ini sudah dipakai akun lain.',
            'employee_id.unique' => 'Pegawai ini sudah tertaut ke akun lain.',
        ];
    }
}
