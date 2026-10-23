<?php

namespace App\Http\Requests\Execution;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaiementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('paiement')) ?? false;
    }

    /**
     * @return array<string, array<int, mixed|string>|string>
     */
    public function rules(): array
    {
        return [
            'montant_paye' => ['required', 'numeric', 'min:0.01'],
            'date_paiement' => ['required', 'date'],
            'reference_paiement' => ['nullable', 'string', 'max:100'],
            'mode_paiement' => ['nullable', 'string', 'max:100'],
            'commentaire' => ['nullable', 'string'],
        ];
    }
}
