<?php

namespace App\Http\Requests\Rbm;

class UpdateAxeStrategiqueRequest extends RbmChildReferentialRequest
{
    protected function parentForeignKey(): string
    {
        return 'pilier_id';
    }

    protected function parentTable(): string
    {
        return 'piliers';
    }

    protected function childTable(): string
    {
        return 'axes_strategiques';
    }

    public function rules(): array
    {
        return $this->childRules($this->route('axe_strategique')?->id);
    }
}
