<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('users.manage') ?? false;
    }

    /**
     * @return array<string, array<int, mixed|string>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'prenom' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'telephone' => ['nullable', 'string', 'max:50'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
            'role' => [
                'required',
                'string',
                Rule::in(Role::query()->where('guard_name', 'web')->pluck('name')->all()),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => __('Nom'),
            'prenom' => __('Prénom'),
            'email' => __('Adresse e-mail'),
            'telephone' => __('Téléphone'),
            'password' => __('Mot de passe'),
            'service_id' => __('Service'),
            'role' => __('Rôle'),
        ];
    }
}
