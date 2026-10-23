<?php

namespace App\Services\Paiement;

use App\Models\Paiement;
use App\Models\PaiementVersion;
use App\Models\User;

/**
 * Versioning des paiements : instantané complet (en-tête + lignes) conservé à
 * chaque modification autorisée ou correction réglementaire.
 */
class PaiementVersionService
{
    public function snapshot(Paiement $paiement, User $user, ?string $motif = null): PaiementVersion
    {
        $paiement->loadMissing('lignes');

        $numero = (int) $paiement->versions()->max('numero_version') + 1;

        $snapshot = [
            'paiement' => $paiement->only([
                'numero', 'paiement_type_id', 'ordonnancement_id', 'paiement_banque_id', 'paiement_lot_id',
                'exercice_budgetaire_id', 'fournisseur_id', 'objet', 'devise', 'devise_paiement', 'taux_change',
                'mode_creation', 'statut', 'date_paiement', 'montant_paye', 'montant_ordonnance_ref',
                'reste_a_payer', 'taux_paiement', 'frais_bancaires',
            ]),
            'lignes' => $paiement->lignes->map(fn ($l) => $l->only([
                'ordonnancement_ligne_id', 'engagement_imputation_id', 'ligne_budgetaire_id', 'source_financement_id',
                'activite_id', 'designation', 'montant_ordonnance', 'montant_paye', 'reste_a_payer', 'devise',
                'taux_change', 'frais_bancaires', 'reference_bancaire',
            ]))->all(),
        ];

        return PaiementVersion::query()->create([
            'paiement_id' => $paiement->id,
            'numero_version' => $numero,
            'snapshot' => $snapshot,
            'motif' => $motif,
            'created_by' => $user->id,
        ]);
    }
}
