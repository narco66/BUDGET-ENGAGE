<?php

namespace App\Http\Requests\Paiement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRejetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('paiement.reject') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'motif' => ['required', Rule::in([
                'rejet_bancaire', 'compte_invalide', 'insuffisance_provision', 'erreur_devise', 'doublon', 'annulation',
            ])],
            'description' => ['nullable', 'string', 'max:2000'],
            'date_rejet' => ['nullable', 'date'],
            'reference_rejet' => ['nullable', 'string', 'max:100'],
        ];
    }
}
