<?php

namespace App\Http\Requests\Rbm;

class StoreTacheRequest extends RbmChildReferentialRequest
{
    protected function parentForeignKey(): string
    {
        return 'activite_id';
    }

    protected function parentTable(): string
    {
        return 'activites';
    }

    protected function childTable(): string
    {
        return 'taches';
    }

    public function rules(): array
    {
        return $this->childRules();
    }
}
