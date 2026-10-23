<?php

namespace App\Http\Requests\Rbm;

class StoreProduitRequest extends RbmChildReferentialRequest
{
    protected function parentForeignKey(): string
    {
        return 'axe_strategique_id';
    }

    protected function parentTable(): string
    {
        return 'axes_strategiques';
    }

    protected function childTable(): string
    {
        return 'produits';
    }

    public function rules(): array
    {
        return $this->childRules();
    }
}
