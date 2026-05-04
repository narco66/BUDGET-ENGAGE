<?php

namespace App\Http\Requests\Execution;

use Illuminate\Foundation\Http\FormRequest;

class StoreLiquidationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('createLiquidation', $this->route('engagement')) ?? false;
    }

    /**
     * @return array<string, array<int, mixed|string>|string>
     */
    public function rules(): array
    {
        return [
            'montant_liquide' => ['required', 'numeric', 'min:0.01'],
            'date_liquidation' => ['required', 'date'],
            'reference' => ['nullable', 'string', 'max:100'],
            'commentaire' => ['nullable', 'string'],
        ];
    }
}
