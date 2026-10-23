<?php

namespace App\Http\Requests\Rbm;

use Illuminate\Validation\Rule;

class StoreUniteMesureRequest extends RbmRootReferentialRequest
{
    public function rules(): array
    {
        return array_merge($this->baseRules(), [
            'code' => ['required', 'string', 'max:50', Rule::unique('unites_mesure', 'code')],
        ]);
    }
}
