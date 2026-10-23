<?php

namespace App\Http\Requests\Engagements;

use App\Models\EngagementType;
use Illuminate\Foundation\Http\FormRequest;

class StoreEngagementTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', EngagementType::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $champs = $this->input('champs_specifiques');
        if (is_string($champs)) {
            $decoded = json_decode($champs, true);
            $this->merge(['champs_specifiques' => is_array($decoded) ? $decoded : []]);
        }

        $docs = $this->input('documents_obligatoires');
        if (is_string($docs)) {
            $this->merge([
                'documents_obligatoires' => array_values(array_filter(array_map('trim', explode(',', $docs)))),
            ]);
        }

        $this->merge([
            'est_actif' => $this->boolean('est_actif'),
            'permet_generation' => $this->boolean('permet_generation'),
            'permet_creation_manuelle' => $this->boolean('permet_creation_manuelle'),
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50', 'unique:engagement_types,code'],
            'libelle' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'famille' => ['nullable', 'string', 'max:60'],
            'nature' => ['nullable', 'string', 'max:60'],
            'workflow_code' => ['nullable', 'string', 'max:100'],
            'ordre' => ['nullable', 'integer', 'min:0'],
            'est_actif' => ['boolean'],
            'permet_generation' => ['boolean'],
            'permet_creation_manuelle' => ['boolean'],
            'champs_specifiques' => ['nullable', 'array'],
            'champs_specifiques.*.cle' => ['required_with:champs_specifiques', 'string', 'max:150'],
            'champs_specifiques.*.libelle' => ['required_with:champs_specifiques', 'string', 'max:255'],
            'champs_specifiques.*.type' => ['nullable', 'string', 'max:30'],
            'documents_obligatoires' => ['nullable', 'array'],
        ];
    }
}
