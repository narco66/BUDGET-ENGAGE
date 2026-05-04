<?php

namespace App\Http\Requests\Referentials;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDirectionRequest extends FormRequest
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
            'departement_id' => ['required', 'integer', 'exists:departements,id'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('directions', 'code')->where(
                    fn ($query) => $query->where('departement_id', $this->integer('departement_id'))
                ),
            ],
            'libelle' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'statut' => ['required', 'in:actif,inactif'],
        ];
    }
}
