<?php

namespace App\Http\Requests\Recettes;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRecettePerceptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('recettes.create') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'exercice_budgetaire_id' => ['required', 'exists:exercices_budgetaires,id'],
            'budget_ligne_id' => [
                'required',
                Rule::exists('budget_lignes', 'id')->where(function ($query): void {
                    $query->where('section', 'recette')
                        ->where('niveau', 'ligne')
                        ->where('exercice_budgetaire_id', $this->input('exercice_budgetaire_id'));
                }),
            ],
            'date_perception' => ['required', 'date'],
            'montant' => ['required', 'numeric', 'min:0.01'],
            'reference' => ['nullable', 'string', 'max:100'],
            'source_emetteur' => ['nullable', 'string', 'max:255'],
            'mode_reglement' => ['required', 'string', 'in:virement,cheque,especes,autre'],
            'observation' => ['nullable', 'string', 'max:2000'],
            'valider' => ['sometimes', 'boolean'],
        ];
    }
}
