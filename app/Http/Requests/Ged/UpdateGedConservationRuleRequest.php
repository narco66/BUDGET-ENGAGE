<?php

namespace App\Http\Requests\Ged;

use App\Enums\SortFinalConservationGed;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGedConservationRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('conservation_rule')) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'conservation_obligatoire' => $this->boolean('conservation_obligatoire'),
            'est_active' => $this->boolean('est_active'),
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $id = $this->route('conservation_rule')?->id;

        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('ged_conservation_rules', 'code')->ignore($id)],
            'libelle' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type_document_id' => ['nullable', 'integer', 'exists:ged_types_documents,id'],
            'duree_conservation_mois' => ['required', 'integer', 'min:0'],
            'sort_final' => ['required', Rule::enum(SortFinalConservationGed::class)],
            'declenchement' => ['required', 'in:date_document,date_archivage,date_depot'],
            'alerte_avant_jours' => ['nullable', 'integer', 'min:0'],
            'conservation_obligatoire' => ['boolean'],
            'est_active' => ['boolean'],
        ];
    }
}
