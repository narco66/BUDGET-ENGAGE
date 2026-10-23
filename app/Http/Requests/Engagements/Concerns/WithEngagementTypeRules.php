<?php

namespace App\Http\Requests\Engagements\Concerns;

use App\Models\EngagementType;
use App\Services\Engagement\EngagementTypeService;
use Illuminate\Validation\Validator;

/**
 * Règles additives (type polymorphe + champs EAV + dimensions analytiques des
 * imputations) partagées par les requêtes de création et de mise à jour
 * d'engagement, sans toucher aux règles existantes.
 */
trait WithEngagementTypeRules
{
    /**
     * @return array<string, array<int, mixed>>
     */
    protected function typeAndDimensionRules(): array
    {
        return [
            'engagement_type_id' => ['nullable', 'integer', 'exists:engagement_types,id'],
            'mode_creation' => ['nullable', 'string', 'max:30'],
            'type_valeurs' => ['nullable', 'array'],
            'type_valeurs.*' => ['nullable', 'string'],
            'imputations.*.source_financement_id' => ['nullable', 'integer', 'exists:sources_financement,id'],
            'imputations.*.activite_id' => ['nullable', 'integer', 'exists:activites,id'],
            'imputations.*.centre_cout' => ['nullable', 'string', 'max:255'],
            'imputations.*.centre_responsabilite' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function validateEngagementTypeFields(Validator $v): void
    {
        $typeId = $this->input('engagement_type_id');
        if (blank($typeId)) {
            return;
        }

        $type = EngagementType::query()->find($typeId);
        if ($type === null) {
            return;
        }

        $valeurs = (array) $this->input('type_valeurs', []);
        foreach (app(EngagementTypeService::class)->missingRequiredFields($type, $valeurs) as $libelle) {
            $v->errors()->add('type_valeurs', __('Le champ « :champ » est obligatoire pour ce type d’engagement.', ['champ' => $libelle]));
        }
    }
}
