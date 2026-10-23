<?php

namespace App\Http\Requests\Budget;

use Illuminate\Foundation\Http\FormRequest;

class StoreImportBudgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('budget.import') ?? false;
    }

    /**
     * @return array<string, array<int, mixed|string>|string>
     */
    public function rules(): array
    {
        return [
            'exercice_budgetaire_id' => ['required', 'integer', 'exists:exercices_budgetaires,id'],
            'fichier' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:20480'],
        ];
    }
}
