<?php

namespace App\Services\Paiement;

use App\Models\Paiement;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Génération des écritures comptables du paiement (partie double SYSCOHADA).
 * Au décaissement : débit compte fournisseur (401), crédit compte banque (521 /
 * 57 caisse). Idempotent : régénère les écritures du paiement.
 */
class PaiementAccountingService
{
    private const COMPTE_FOURNISSEUR = '401';

    private const COMPTE_BANQUE = '521';

    private const COMPTE_CAISSE = '571';

    public function genererEcritures(Paiement $paiement, User $user): void
    {
        $paiement->loadMissing(['type', 'banque', 'fournisseur']);

        $montant = (string) $paiement->montant_paye;
        if (bccomp($montant, '0', 2) <= 0) {
            return;
        }

        DB::transaction(function () use ($paiement, $user, $montant): void {
            $paiement->ecritures()->delete();

            $numero = 'ECR-'.($paiement->numero ?? $paiement->id);
            $date = $paiement->execute_at?->toDateString() ?? now()->toDateString();
            $devise = $paiement->devise_paiement ?? $paiement->devise ?? 'XAF';
            $compteBanque = $paiement->banque !== null ? self::COMPTE_BANQUE : self::COMPTE_CAISSE;
            $beneficiaire = $paiement->fournisseur?->raison_sociale ?? 'Bénéficiaire';

            // Débit : extinction de la dette fournisseur.
            $paiement->ecritures()->create([
                'numero_ecriture' => $numero,
                'date_ecriture' => $date,
                'journal' => 'BQ',
                'compte' => self::COMPTE_FOURNISSEUR,
                'libelle' => 'Règlement '.$beneficiaire.' — '.($paiement->numero ?? ''),
                'debit' => $montant,
                'credit' => 0,
                'devise' => $devise,
                'lettrage' => $paiement->numero,
                'created_by' => $user->id,
            ]);

            // Crédit : sortie de trésorerie.
            $paiement->ecritures()->create([
                'numero_ecriture' => $numero,
                'date_ecriture' => $date,
                'journal' => 'BQ',
                'compte' => $compteBanque,
                'libelle' => 'Décaissement '.($paiement->banque?->code ?? 'caisse'),
                'debit' => 0,
                'credit' => $montant,
                'devise' => $devise,
                'lettrage' => $paiement->numero,
                'created_by' => $user->id,
            ]);

            // Frais bancaires éventuels : charge (627) en débit, banque en crédit.
            $frais = (string) ($paiement->frais_bancaires ?? '0');
            if (bccomp($frais, '0', 2) > 0) {
                $paiement->ecritures()->create([
                    'numero_ecriture' => $numero,
                    'date_ecriture' => $date,
                    'journal' => 'BQ',
                    'compte' => '627',
                    'libelle' => 'Frais bancaires — '.($paiement->numero ?? ''),
                    'debit' => $frais,
                    'credit' => 0,
                    'devise' => $devise,
                    'created_by' => $user->id,
                ]);
                $paiement->ecritures()->create([
                    'numero_ecriture' => $numero,
                    'date_ecriture' => $date,
                    'journal' => 'BQ',
                    'compte' => $compteBanque,
                    'libelle' => 'Frais bancaires (décaissement)',
                    'debit' => 0,
                    'credit' => $frais,
                    'devise' => $devise,
                    'created_by' => $user->id,
                ]);
            }
        });
    }
}
