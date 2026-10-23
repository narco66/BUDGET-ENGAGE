<?php

namespace App\Http\Requests\Ged;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGedWorkflowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('workflow')) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'est_actif' => $this->boolean('est_actif'),
            'est_par_defaut' => $this->boolean('est_par_defaut'),
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $id = $this->route('workflow')?->id;

        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('ged_workflows', 'code')->ignore($id)],
            'libelle' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type_document_id' => ['nullable', 'integer', 'exists:ged_types_documents,id'],
            'est_actif' => ['boolean'],
            'est_par_defaut' => ['boolean'],
            'etapes' => ['required', 'array', 'min:1'],
            'etapes.*.libelle' => ['required', 'string', 'max:255'],
            'etapes.*.type_etape' => ['required', 'in:depot,controle,validation,approbation,signature,publication,archivage'],
            'etapes.*.permission_requise' => ['nullable', 'string', 'max:100'],
            'etapes.*.role_requis' => ['nullable', 'string', 'max:100'],
            'etapes.*.delai_jours' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
