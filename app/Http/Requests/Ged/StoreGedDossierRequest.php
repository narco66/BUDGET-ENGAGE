<?php

namespace App\Http\Requests\Ged;

use App\Models\GedDossier;
use Illuminate\Foundation\Http\FormRequest;

class StoreGedDossierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', GedDossier::class) ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'libelle' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:100'],
            'reference' => ['nullable', 'string', 'max:150', 'unique:ged_dossiers,reference'],
            'description' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'integer', 'exists:ged_dossiers,id'],
            'categorie_id' => ['nullable', 'integer', 'exists:ged_categories,id'],
            'exercice_budgetaire_id' => ['nullable', 'integer', 'exists:exercices_budgetaires,id'],
            'departement_id' => ['nullable', 'integer', 'exists:departements,id'],
            'direction_id' => ['nullable', 'integer', 'exists:directions,id'],
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
            'module_source' => ['nullable', 'string', 'max:100'],
            'statut' => ['required', 'in:actif,inactif'],
        ];
    }
}
