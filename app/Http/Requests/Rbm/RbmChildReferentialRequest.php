<?php

namespace App\Http\Requests\Rbm;

use Illuminate\Validation\Rule;

abstract class RbmChildReferentialRequest extends RbmRootReferentialRequest
{
    abstract protected function parentForeignKey(): string;

    abstract protected function parentTable(): string;

    abstract protected function childTable(): string;

    /**
     * @return array<string, array<int, mixed|string>|string>
     */
    protected function childRules(?int $ignoreId = null): array
    {
        $parentKey = $this->parentForeignKey();

        return array_merge($this->baseRules(), [
            $parentKey => ['required', 'integer', 'exists:'.$this->parentTable().',id'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique($this->childTable(), 'code')
                    ->where(fn ($q) => $q->where($parentKey, $this->integer($parentKey)))
                    ->ignore($ignoreId),
            ],
            'libelle' => [
                'required',
                'string',
                'max:255',
                Rule::unique($this->childTable(), 'libelle')
                    ->where(fn ($q) => $q->where($parentKey, $this->integer($parentKey)))
                    ->ignore($ignoreId),
            ],
        ]);
    }
}
