<?php

namespace App\Http\Requests\Rbm;

class UpdateActiviteRequest extends RbmChildReferentialRequest
{
    protected function parentForeignKey(): string
    {
        return 'sous_produit_id';
    }

    protected function parentTable(): string
    {
        return 'sous_produits';
    }

    protected function childTable(): string
    {
        return 'activites';
    }

    public function rules(): array
    {
        return $this->childRules($this->route('activite')?->id);
    }
}
