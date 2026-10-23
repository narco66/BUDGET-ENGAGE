<?php

namespace App\Http\Requests\Budget;

use App\Services\Budget\BudgetImportService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBudgetImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if ($user === null) {
            return false;
        }

        if ($this->input('mode') === 'remplacement') {
            return $user->can('budget.replace');
        }

        return $user->can('budget.import.run') || $user->can('budget.replace');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'exercice_budgetaire_id' => ['required', 'integer', 'exists:exercices_budgetaires,id'],
            'version' => ['nullable', 'string', 'max:30'],
            'mode' => ['required', Rule::in(BudgetImportService::MODES)],
            'source' => ['required', Rule::in(['document', 'fichier'])],
            'fichier' => ['required_if:source,fichier', 'nullable', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
            'confirmation' => [Rule::requiredIf($this->input('mode') === 'remplacement'), 'accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'confirmation.accepted' => __('Vous devez confirmer explicitement le remplacement des donnees budgetaires existantes.'),
            'confirmation.required' => __('Vous devez confirmer explicitement le remplacement des donnees budgetaires existantes.'),
        ];
    }
}
