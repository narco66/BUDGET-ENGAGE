<?php

namespace App\Http\Requests\Rbm;

use Illuminate\Validation\Rule;

class UpdateSourceVerificationRequest extends RbmRootReferentialRequest
{
    public function rules(): array
    {
        return array_merge($this->baseRules(), [
            'code' => [
                'required', 'string', 'max:50',
                Rule::unique('sources_verification', 'code')->ignore($this->route('source_verification')),
            ],
        ]);
    }
}
