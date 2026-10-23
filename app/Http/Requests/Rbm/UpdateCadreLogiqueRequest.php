<?php

namespace App\Http\Requests\Rbm;

use Illuminate\Validation\Rule;

class UpdateCadreLogiqueRequest extends RbmRootReferentialRequest
{
    public function rules(): array
    {
        return array_merge($this->baseRules(), [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('cadres_logiques', 'code')->ignore($this->route('cadre_logique')),
            ],
            'annee' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'version' => ['nullable', 'string', 'max:20'],
        ]);
    }
}
