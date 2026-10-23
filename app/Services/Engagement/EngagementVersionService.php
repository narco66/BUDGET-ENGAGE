<?php

namespace App\Services\Engagement;

use App\Models\Engagement;
use App\Models\EngagementVersion;
use App\Models\User;

/**
 * Versioning des engagements : conserve un instantané complet (en-tête +
 * imputations + champs spécifiques) à chaque modification autorisée.
 */
class EngagementVersionService
{
    public function snapshot(Engagement $engagement, User $user, ?string $motif = null): EngagementVersion
    {
        $engagement->loadMissing(['imputations', 'typeValeurs']);

        $numero = (int) $engagement->versions()->max('numero_version') + 1;

        $snapshot = [
            'engagement' => $engagement->only([
                'numero', 'engagement_type_id', 'type_engagement', 'exercice_budgetaire_id',
                'beneficiaire_type', 'beneficiaire_id', 'beneficiaire_autre', 'objet',
                'montant', 'date_engagement', 'statut',
            ]),
            'imputations' => $engagement->imputations->map(fn ($i) => $i->only([
                'ligne_budgetaire_id', 'source_financement_id', 'activite_id',
                'centre_cout', 'centre_responsabilite', 'montant_impute', 'observation',
            ]))->all(),
            'type_valeurs' => $engagement->typeValeurs->pluck('valeur', 'cle')->all(),
        ];

        return EngagementVersion::query()->create([
            'engagement_id' => $engagement->id,
            'numero_version' => $numero,
            'snapshot' => $snapshot,
            'motif' => $motif,
            'created_by' => $user->id,
        ]);
    }
}
