<?php

namespace App\Http\Requests\Rbm;

class StoreSousProduitRequest extends RbmChildReferentialRequest
{
    protected function parentForeignKey(): string
    {
        return 'produit_id';
    }

    protected function parentTable(): string
    {
        return 'produits';
    }

    protected function childTable(): string
    {
        return 'sous_produits';
    }

    public function rules(): array
    {
        return $this->childRules();
    }
}
