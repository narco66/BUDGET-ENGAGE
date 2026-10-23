<?php

namespace App\Http\Requests\SuiviPhysique;

use Illuminate\Foundation\Http\FormRequest;

class StoreSuiviPhysiqueRealisationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('suivi_physique.create') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'activite_id' => ['required', 'exists:activites,id'],
            'exercice_budgetaire_id' => ['required', 'exists:exercices_budgetaires,id'],
            'trimestre' => ['required', 'integer', 'between:1,4'],
            'annee' => ['required', 'integer', 'min:2000', 'max:2100'],
            'periode_debut' => ['required', 'date'],
            'periode_fin' => ['required', 'date', 'after_or_equal:periode_debut'],
            'taux_avancement' => ['required', 'numeric', 'min:0', 'max:100'],
            'quantite_realisee' => ['nullable', 'numeric', 'min:0'],
            'libelle_realisation' => ['nullable', 'string', 'max:255'],
            'commentaire' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
