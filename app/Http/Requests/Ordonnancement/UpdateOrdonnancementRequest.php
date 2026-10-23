<?php

namespace App\Http\Requests\Ordonnancement;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrdonnancementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('ordonnancement.update') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'ordonnancement_type_id' => ['nullable', 'integer', 'exists:ordonnancement_types,id'],
            'fournisseur_id' => ['nullable', 'integer', 'exists:fournisseurs,id'],
            'objet' => ['nullable', 'string', 'max:1000'],
            'devise' => ['nullable', 'string', 'max:10'],
            'date_ordonnancement' => ['required', 'date'],
            'reference' => ['nullable', 'string', 'max:100'],
            'commentaire' => ['nullable', 'string'],
            'motif' => ['nullable', 'string', 'max:1000'],

            'lignes' => ['sometimes', 'array', 'min:1'],
            'lignes.*.designation' => ['required_with:lignes', 'string', 'max:255'],
            'lignes.*.ligne_budgetaire_id' => ['required_with:lignes', 'integer', 'exists:lignes_budgetaires,id'],
            'lignes.*.liquidation_ligne_id' => ['nullable', 'integer', 'exists:liquidation_lignes,id'],
            'lignes.*.engagement_imputation_id' => ['nullable', 'integer', 'exists:engagement_imputations,id'],
            'lignes.*.source_financement_id' => ['nullable', 'integer', 'exists:sources_financement,id'],
            'lignes.*.activite_id' => ['nullable', 'integer', 'exists:activites,id'],
            'lignes.*.centre_cout' => ['nullable', 'string', 'max:100'],
            'lignes.*.centre_responsabilite' => ['nullable', 'string', 'max:100'],
            'lignes.*.montant_liquide' => ['nullable', 'numeric', 'min:0'],
            'lignes.*.montant_ordonnance' => ['required_with:lignes', 'numeric', 'min:0'],
            'lignes.*.mode_paiement' => ['nullable', 'string', 'max:60'],
            'lignes.*.echeance' => ['nullable', 'date'],
            'lignes.*.banque' => ['nullable', 'string', 'max:150'],
            'lignes.*.compte_bancaire' => ['nullable', 'string', 'max:60'],
            'lignes.*.observation' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
