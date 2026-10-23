<?php

namespace App\Http\Requests\Rbm;

use Illuminate\Validation\Rule;

class UpdateIndicateurRequest extends RbmRootReferentialRequest
{
    public function rules(): array
    {
        return array_merge($this->baseRules(), [
            'type_indicateur_id' => ['required', 'integer', 'exists:types_indicateurs,id'],
            'unite_mesure_id' => ['required', 'integer', 'exists:unites_mesure,id'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('indicateurs', 'code')->ignore($this->route('indicateur')),
            ],
            'tache_ids' => ['nullable', 'array'],
            'tache_ids.*' => ['integer', 'exists:taches,id'],
            'source_verification_ids' => ['nullable', 'array'],
            'source_verification_ids.*' => ['integer', 'exists:sources_verification,id'],
        ]);
    }
}
