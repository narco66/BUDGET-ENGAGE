<?php

namespace App\Services\Paiement;

use App\Models\Paiement;
use App\Models\PaiementBanque;
use Illuminate\Validation\ValidationException;

/**
 * Moteur de trésorerie : solde disponible, plafonds de décaissement,
 * blocage en cas d'insuffisance sauf dérogation tracée.
 */
class PaiementTreasuryService
{
    private const SCALE = 2;

    /**
     * Montant déjà décaissé (paiements exécutés non annulés) sur un compte.
     */
    public function montantDecaisse(PaiementBanque $banque): string
    {
        return (string) Paiement::query()
            ->where('paiement_banque_id', $banque->id)
            ->where('statut', 'execute')
            ->whereNull('cancelled_at')
            ->sum('montant_paye');
    }

    /**
     * Solde disponible = solde initial − décaissé.
     */
    public function soldeDisponible(PaiementBanque $banque): string
    {
        return bcsub((string) ($banque->solde_initial ?? '0'), $this->montantDecaisse($banque), self::SCALE);
    }

    /**
     * Plafond restant = plafond − décaissé (null si pas de plafond).
     */
    public function plafondRestant(PaiementBanque $banque): ?string
    {
        if ($banque->plafond_decaissement === null) {
            return null;
        }

        return bcsub((string) $banque->plafond_decaissement, $this->montantDecaisse($banque), self::SCALE);
    }

    /**
     * Vérifie qu'un décaissement est possible (plafond et solde) ; bloque sinon,
     * sauf dérogation explicite (tracée par l'appelant).
     */
    public function assertDecaissementPossible(Paiement $paiement, string $montant, bool $derogation = false): void
    {
        $banque = $paiement->banque;

        if ($banque === null) {
            return; // Espèces / compensation : pas de contrôle bancaire.
        }

        if ($derogation) {
            return;
        }

        $plafondRestant = $this->plafondRestant($banque);
        if ($plafondRestant !== null && bccomp($montant, $plafondRestant, self::SCALE) > 0) {
            throw ValidationException::withMessages([
                'tresorerie' => __('Plafond de décaissement dépassé sur le compte :compte (restant : :r).', [
                    'compte' => $banque->code,
                    'r' => $plafondRestant,
                ]),
            ]);
        }

        if ($banque->solde_initial !== null) {
            $solde = $this->soldeDisponible($banque);
            if (bccomp($montant, $solde, self::SCALE) > 0) {
                throw ValidationException::withMessages([
                    'tresorerie' => __('Solde insuffisant sur le compte :compte (disponible : :s).', [
                        'compte' => $banque->code,
                        's' => $solde,
                    ]),
                ]);
            }
        }
    }
}
