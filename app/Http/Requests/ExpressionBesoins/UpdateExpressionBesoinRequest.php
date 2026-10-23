<?php

namespace App\Http\Requests\ExpressionBesoins;

class UpdateExpressionBesoinRequest extends StoreExpressionBesoinRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('expression_besoin')) ?? false;
    }
}
