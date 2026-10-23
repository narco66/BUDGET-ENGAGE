<?php

namespace App\Http\Requests\SuiviEvaluation;

use Illuminate\Foundation\Http\FormRequest;

class StoreIndicateurRealisationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('suivi_evaluation.create') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'indicateur_id' => ['required', 'exists:indicateurs,id'],
            'exercice_budgetaire_id' => ['required', 'exists:exercices_budgetaires,id'],
            'trimestre' => ['required', 'integer', 'between:1,4'],
            'annee' => ['required', 'integer', 'min:2000', 'max:2100'],
            'periode_debut' => ['required', 'date'],
            'periode_fin' => ['required', 'date', 'after_or_equal:periode_debut'],
            'valeur_cible' => ['nullable', 'numeric', 'min:0'],
            'valeur_realisee' => ['required', 'numeric', 'min:0'],
            'source_donnee' => ['nullable', 'string', 'max:255'],
            'commentaire' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
