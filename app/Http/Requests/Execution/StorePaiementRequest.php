<?php

namespace App\Http\Requests\Execution;

use Illuminate\Foundation\Http\FormRequest;

class StorePaiementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('addPaiement', $this->route('ordonnancement')) ?? false;
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
