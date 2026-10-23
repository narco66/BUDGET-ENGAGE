<?php

namespace App\Http\Requests\Rbm;

use Illuminate\Validation\Rule;

class StorePilierRequest extends RbmRootReferentialRequest
{
    /**
     * @return array<string, array<int, mixed|string>|string>
     */
    public function rules(): array
    {
        return array_merge($this->baseRules(), [
            'code' => ['required', 'string', 'max:50', Rule::unique('piliers', 'code')],
        ]);
    }
}
