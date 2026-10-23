<?php

namespace App\Services\Ordonnancement;

use App\Models\Ordonnancement;
use App\Models\OrdonnancementVersion;
use App\Models\User;

/**
 * Versioning des ordonnancements : instantané complet (en-tête + lignes)
 * conservé à chaque modification autorisée ou correction réglementaire.
 */
class OrdonnancementVersionService
{
    public function snapshot(Ordonnancement $ordonnancement, User $user, ?string $motif = null): OrdonnancementVersion
    {
        $ordonnancement->loadMissing('lignes');

        $numero = (int) $ordonnancement->versions()->max('numero_version') + 1;

        $snapshot = [
            'ordonnancement' => $ordonnancement->only([
                'numero', 'ordonnancement_type_id', 'liquidation_id', 'exercice_budgetaire_id', 'fournisseur_id',
                'beneficiaire_type', 'beneficiaire_id', 'objet', 'devise', 'mode_creation', 'statut',
                'date_ordonnancement', 'montant_ordonnance', 'montant_liquide_ref', 'reste_a_ordonnancer',
                'taux_ordonnancement',
            ]),
            'lignes' => $ordonnancement->lignes->map(fn ($l) => $l->only([
                'liquidation_ligne_id', 'engagement_imputation_id', 'ligne_budgetaire_id', 'source_financement_id',
                'activite_id', 'tache_id', 'pap_ligne_budgetaire_id', 'centre_cout', 'centre_responsabilite',
                'designation', 'montant_liquide', 'montant_ordonnance', 'reste_a_ordonnancer', 'mode_paiement',
                'echeance', 'banque', 'compte_bancaire',
            ]))->all(),
        ];

        return OrdonnancementVersion::query()->create([
            'ordonnancement_id' => $ordonnancement->id,
            'numero_version' => $numero,
            'snapshot' => $snapshot,
            'motif' => $motif,
            'created_by' => $user->id,
        ]);
    }
}
