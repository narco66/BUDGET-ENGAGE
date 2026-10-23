<?php

namespace App\Services\Paiement;

use App\Models\Paiement;
use App\Models\PaiementBanque;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Moteur bancaire : validité des comptes (IBAN/BIC), génération des références
 * de transaction, intitulés de virement.
 */
class PaiementBankingService
{
    /**
     * Vérifie qu'un compte bancaire est exploitable pour un décaissement.
     */
    public function assertBanqueValide(?PaiementBanque $banque): void
    {
        if ($banque === null) {
            throw ValidationException::withMessages([
                'banque' => __('Aucun compte bancaire n’est associé au paiement.'),
            ]);
        }

        if (! $banque->est_actif) {
            throw ValidationException::withMessages([
                'banque' => __('Le compte bancaire :compte est inactif.', ['compte' => $banque->code]),
            ]);
        }

        if (blank($banque->iban) && blank($banque->rib)) {
            throw ValidationException::withMessages([
                'banque' => __('Le compte bancaire :compte ne dispose ni d’IBAN ni de RIB.', ['compte' => $banque->code]),
            ]);
        }
    }

    /**
     * Génère une référence bancaire de transaction unique pour un paiement.
     */
    public function genererReferenceBancaire(Paiement $paiement): string
    {
        return sprintf('TRX-%s-%s', $paiement->numero ?? $paiement->id, strtoupper(Str::random(6)));
    }
}
