<?php

namespace App\Http\Requests\Budget;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExerciceBudgetaireRequest extends FormRequest
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
            'annee' => ['required', 'integer', 'min:2000', 'max:2100', 'unique:exercices_budgetaires,annee'],
            'date_debut' => ['required', 'date'],
            'date_fin' => ['required', 'date', 'after_or_equal:date_debut'],
            'statut' => ['required', Rule::in(['preparatoire', 'ouvert', 'suspendu', 'cloture', 'archive'])],
            'est_actif' => ['sometimes', 'boolean'],
        ];
    }
}
