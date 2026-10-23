<?php

namespace App\Http\Requests\Rbm;

use Illuminate\Validation\Rule;

class UpdateTypeIndicateurRequest extends RbmRootReferentialRequest
{
    public function rules(): array
    {
        return array_merge($this->baseRules(), [
            'code' => [
                'required', 'string', 'max:50',
                Rule::unique('types_indicateurs', 'code')->ignore($this->route('type_indicateur')),
            ],
        ]);
    }
}
