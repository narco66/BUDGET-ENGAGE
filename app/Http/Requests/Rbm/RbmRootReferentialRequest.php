<?php

namespace App\Http\Requests\Rbm;

use Illuminate\Foundation\Http\FormRequest;

abstract class RbmRootReferentialRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && (
            $user->can('rbm.admin')
            || $user->can('rbm.create')
            || $user->can('rbm.update')
        );
    }

    /**
     * @return array<string, array<int, mixed|string>|string>
     */
    protected function baseRules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50'],
            'libelle' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'ordre' => ['nullable', 'integer', 'min:0'],
            'statut' => ['required', 'in:actif,inactif'],
        ];
    }
}
