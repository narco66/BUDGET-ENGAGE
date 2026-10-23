<?php

namespace App\Services\Liquidation;

use App\Models\Liquidation;
use App\Models\LiquidationVersion;
use App\Models\User;

/**
 * Versioning des liquidations : instantané complet (en-tête + lignes + service
 * fait) conservé à chaque modification autorisée ou correction réglementaire.
 */
class LiquidationVersionService
{
    public function snapshot(Liquidation $liquidation, User $user, ?string $motif = null): LiquidationVersion
    {
        $liquidation->loadMissing(['lignes', 'serviceFait']);

        $numero = (int) $liquidation->versions()->max('numero_version') + 1;

        $snapshot = [
            'liquidation' => $liquidation->only([
                'numero', 'liquidation_type_id', 'engagement_id', 'engagement_imputation_id',
                'exercice_budgetaire_id', 'fournisseur_id', 'beneficiaire_type', 'beneficiaire_id',
                'objet', 'devise', 'mode_creation', 'statut', 'date_liquidation',
                'montant_brut', 'total_avances', 'total_acomptes', 'total_retenues', 'total_garanties',
                'total_penalites', 'total_taxes', 'montant_revision_prix', 'montant_net', 'montant_liquide',
                'taux_liquidation',
            ]),
            'lignes' => $liquidation->lignes->map(fn ($l) => $l->only([
                'engagement_imputation_id', 'ligne_budgetaire_id', 'source_financement_id', 'activite_id',
                'tache_id', 'pap_ligne_budgetaire_id', 'centre_cout', 'centre_responsabilite', 'designation',
                'quantite_prevue', 'quantite_realisee', 'prix_unitaire', 'montant_brut', 'montant_avances',
                'montant_acomptes', 'montant_retenues', 'montant_garanties', 'montant_penalites',
                'montant_taxes', 'montant_revision_prix', 'montant_net',
            ]))->all(),
            'service_fait' => $liquidation->serviceFait?->only([
                'date_constatation', 'conformite_quantitative', 'conformite_qualitative',
                'conformite_technique', 'conformite_contractuelle', 'reserves', 'reserves_levees', 'valide',
            ]),
        ];

        return LiquidationVersion::query()->create([
            'liquidation_id' => $liquidation->id,
            'numero_version' => $numero,
            'snapshot' => $snapshot,
            'motif' => $motif,
            'created_by' => $user->id,
        ]);
    }
}
