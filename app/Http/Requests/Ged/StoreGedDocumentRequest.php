<?php

namespace App\Http\Requests\Ged;

use App\Enums\NiveauConfidentialiteGed;
use App\Models\GedDocument;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGedDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', GedDocument::class) ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'titre' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'categorie_id' => ['nullable', 'integer', 'exists:ged_categories,id'],
            'type_document_id' => ['nullable', 'integer', 'exists:ged_types_documents,id'],
            'dossier_id' => ['nullable', 'integer', 'exists:ged_dossiers,id'],
            'conservation_rule_id' => ['nullable', 'integer', 'exists:ged_conservation_rules,id'],
            'workflow_id' => ['nullable', 'integer', 'exists:ged_workflows,id'],
            'niveau_confidentialite' => ['nullable', Rule::enum(NiveauConfidentialiteGed::class)],
            'date_document' => ['nullable', 'date'],
            'date_expiration' => ['nullable', 'date', 'after_or_equal:date_document'],
            'module_source' => ['nullable', 'string', 'max:100'],
            'module_source_id' => ['nullable', 'integer'],
            'exercice_budgetaire_id' => ['nullable', 'integer', 'exists:exercices_budgetaires,id'],
            'departement_id' => ['nullable', 'integer', 'exists:departements,id'],
            'direction_id' => ['nullable', 'integer', 'exists:directions,id'],
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
            'fichier' => [
                'required', 'file', 'max:51200',
                'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,png,jpg,jpeg,gif,txt,csv,zip',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'titre' => __('Titre'),
            'type_document_id' => __('Type de document'),
            'fichier' => __('Fichier'),
        ];
    }
}
