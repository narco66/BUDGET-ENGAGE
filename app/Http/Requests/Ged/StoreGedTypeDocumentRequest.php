<?php

namespace App\Http\Requests\Ged;

use App\Enums\NiveauConfidentialiteGed;
use App\Enums\SortFinalConservationGed;
use App\Models\GedTypeDocument;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGedTypeDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', GedTypeDocument::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['est_signable' => $this->boolean('est_signable')]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50', 'unique:ged_types_documents,code'],
            'libelle' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'categorie_id' => ['nullable', 'integer', 'exists:ged_categories,id'],
            'prefixe_reference' => ['nullable', 'string', 'max:30'],
            'duree_conservation_mois' => ['nullable', 'integer', 'min:0'],
            'sort_final' => ['nullable', Rule::enum(SortFinalConservationGed::class)],
            'niveau_confidentialite_defaut' => ['nullable', Rule::enum(NiveauConfidentialiteGed::class)],
            'est_signable' => ['boolean'],
            'ordre' => ['nullable', 'integer', 'min:0'],
            'statut' => ['required', 'in:actif,inactif'],
        ];
    }
}
