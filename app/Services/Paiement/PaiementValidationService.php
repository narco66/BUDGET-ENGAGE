<?php

namespace App\Services\Paiement;

use App\Models\Paiement;
use Illuminate\Validation\ValidationException;

/**
 * Contrôles métier de cohérence du paiement : plafond ordonnancement, pièces
 * obligatoires, présence de lignes, compte bancaire requis selon le type.
 */
class PaiementValidationService
{
    private const SCALE = 2;

    public function __construct(
        private readonly PaiementCalculationService $calcul,
        private readonly PaiementTreasuryService $treasury,
    ) {
    }

    /** Cumul déjà payé (exécuté) sur l'ordonnancement, hors paiement courant. */
    public function montantDejaPaye(Paiement $paiement): string
    {
        return (string) Paiement::query()
            ->where('ordonnancement_id', $paiement->ordonnancement_id)
            ->whereKeyNot($paiement->id)
            ->whereNull('cancelled_at')
            ->sum('montant_paye');
    }

    public function resteOrdonnancement(Paiement $paiement): string
    {
        $paiement->loadMissing('ordonnancement');
        $montantOrdonnance = (string) ($paiement->ordonnancement?->montant_ordonnance ?? '0');

        return bcsub($montantOrdonnance, $this->montantDejaPaye($paiement), self::SCALE);
    }

    public function assertLignesPresentes(Paiement $paiement): void
    {
        if ($paiement->lignes()->count() === 0) {
            throw ValidationException::withMessages([
                'lignes' => __('Le paiement doit comporter au moins une ligne.'),
            ]);
        }
    }

    public function assertMontantDansOrdonnancement(Paiement $paiement): void
    {
        $total = $this->calcul->totalAPayer($paiement);
        $reste = $this->resteOrdonnancement($paiement);

        if (bccomp($total, $reste, self::SCALE) > 0) {
            throw ValidationException::withMessages([
                'montant' => __('Le montant à payer (:t) dépasse le reste sur l’ordonnancement (:r).', [
                    't' => $total,
                    'r' => $reste,
                ]),
            ]);
        }
    }

    public function assertDocumentsObligatoires(Paiement $paiement): void
    {
        $paiement->loadMissing('type');
        $requis = (array) ($paiement->type?->documents_obligatoires ?? []);

        if ($requis === []) {
            return;
        }

        $presents = $paiement->documents()->whereNotNull('type_piece')->pluck('type_piece')->all();
        $manquants = array_values(array_diff($requis, $presents));

        if ($manquants !== []) {
            throw ValidationException::withMessages([
                'documents' => __('Pièces obligatoires manquantes : :pieces.', ['pieces' => implode(', ', $manquants)]),
            ]);
        }
    }

    /** Le compte bancaire est requis sauf si le type l'en dispense (espèces…). */
    public function assertBanqueRequise(Paiement $paiement): void
    {
        $paiement->loadMissing('type');
        $exige = $paiement->type?->exige_banque ?? true;

        if ($exige && $paiement->paiement_banque_id === null) {
            throw ValidationException::withMessages([
                'banque' => __('Un compte bancaire est requis pour ce type de paiement.'),
            ]);
        }
    }

    public function assertPretPourSoumission(Paiement $paiement): void
    {
        $this->assertLignesPresentes($paiement);

        if (bccomp($this->calcul->totalAPayer($paiement), '0', self::SCALE) <= 0) {
            throw ValidationException::withMessages([
                'montant' => __('Le montant à payer doit être strictement positif.'),
            ]);
        }

        $this->assertMontantDansOrdonnancement($paiement);
    }

    public function assertPretPourExecution(Paiement $paiement, bool $derogationTresorerie = false): void
    {
        $this->assertBanqueRequise($paiement);
        $this->assertDocumentsObligatoires($paiement);
        $this->assertMontantDansOrdonnancement($paiement);
        $this->treasury->assertDecaissementPossible($paiement, $this->calcul->totalAPayer($paiement), $derogationTresorerie);
    }
}
