<?php

namespace App\Http\Requests\Budget;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLigneBudgetaireRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('exercises.manage') ?? false;
    }

    /**
     * @return array<string, array<int, mixed|string>|string>
     */
    public function rules(): array
    {
        return [
            'exercice_budgetaire_id' => ['required', 'integer', 'exists:exercices_budgetaires,id'],
            'departement_id' => ['nullable', 'integer', 'exists:departements,id'],
            'direction_id' => ['nullable', 'integer', 'exists:directions,id'],
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
            'nature_depense_id' => ['required', 'integer', 'exists:natures_depenses,id'],
            'source_financement_id' => ['nullable', 'integer', 'exists:sources_financement,id'],
            'pap_ligne_budgetaire_id' => ['nullable', 'integer', 'exists:pap_lignes_budgetaires,id'],
            'code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('lignes_budgetaires', 'code')
                    ->where(fn ($query) => $query->where('exercice_budgetaire_id', $this->integer('exercice_budgetaire_id')))
                    ->ignore($this->route('ligne_budgetaire')->id),
            ],
            'libelle' => ['required', 'string', 'max:255'],
            'montant_vote' => ['required', 'numeric', 'min:0'],
            'montant_revise' => ['required', 'numeric', 'min:0'],
            'montant_engage' => ['sometimes', 'numeric', 'min:0'],
            'montant_liquide' => ['sometimes', 'numeric', 'min:0'],
            'montant_ordonnance' => ['sometimes', 'numeric', 'min:0'],
            'montant_paye' => ['sometimes', 'numeric', 'min:0'],
            'statut' => ['required', Rule::in(['active', 'suspendue', 'cloturee'])],
        ];
    }
}
