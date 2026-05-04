<?php

namespace App\Http\Requests\Referentials;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFournisseurRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('referentials.manage') ?? false;
    }

    /**
     * @return array<string, array<int, mixed|string>|string>
     */
    public function rules(): array
    {
        return [
            'raison_sociale' => ['required', 'string', 'max:255'],
            'sigle' => ['nullable', 'string', 'max:100'],
            'numero_fiscal' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('fournisseurs', 'numero_fiscal')->ignore($this->route('fournisseur')),
            ],
            'adresse' => ['nullable', 'string'],
            'telephone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'banque' => ['nullable', 'string', 'max:255'],
            'compte_bancaire' => ['nullable', 'string', 'max:100'],
            'type_fournisseur' => ['nullable', 'string', 'max:100'],
            'statut' => ['required', 'in:actif,inactif,suspendu'],
        ];
    }
}
