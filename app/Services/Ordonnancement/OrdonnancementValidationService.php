<?php

namespace App\Services\Ordonnancement;

use App\Models\Ordonnancement;
use Illuminate\Validation\ValidationException;

/**
 * Moteur de contrôle financier de l'ordonnancement : disponibilité des crédits,
 * conformité budgétaire / comptable / juridique / documentaire, respect des
 * plafonds et cohérence des imputations. Bloque tout ordonnancement si un
 * contrôle échoue.
 */
class OrdonnancementValidationService
{
    private const SCALE = 2;

    public function __construct(private readonly OrdonnancementCalculationService $calcul)
    {
    }

    /** Cumul déjà ordonnancé (mandaté) sur la liquidation, hors ordonnancement courant. */
    public function montantDejaOrdonnance(Ordonnancement $ordonnancement): string
    {
        return (string) Ordonnancement::query()
            ->where('liquidation_id', $ordonnancement->liquidation_id)
            ->whereKeyNot($ordonnancement->id)
            ->whereNull('cancelled_at')
            ->sum('montant_ordonnance');
    }

    public function resteLiquidation(Ordonnancement $ordonnancement): string
    {
        $ordonnancement->loadMissing('liquidation');
        $montantLiquide = (string) ($ordonnancement->liquidation?->montant_liquide ?? '0');

        return bcsub($montantLiquide, $this->montantDejaOrdonnance($ordonnancement), self::SCALE);
    }

    public function assertLignesPresentes(Ordonnancement $ordonnancement): void
    {
        if ($ordonnancement->lignes()->count() === 0) {
            throw ValidationException::withMessages([
                'lignes' => __('L’ordonnancement doit comporter au moins une ligne.'),
            ]);
        }
    }

    public function assertMontantDansLiquidation(Ordonnancement $ordonnancement): void
    {
        $total = $this->calcul->totalAOrdonnancer($ordonnancement);
        $reste = $this->resteLiquidation($ordonnancement);

        if (bccomp($total, $reste, self::SCALE) > 0) {
            throw ValidationException::withMessages([
                'montant' => __('Le montant à ordonnancer (:t) dépasse le reste sur la liquidation (:r).', [
                    't' => $total,
                    'r' => $reste,
                ]),
            ]);
        }
    }

    /** Conformité documentaire : pièces obligatoires du type présentes. */
    public function assertDocumentsObligatoires(Ordonnancement $ordonnancement): void
    {
        $ordonnancement->loadMissing('type');
        $requis = (array) ($ordonnancement->type?->documents_obligatoires ?? []);

        if ($requis === []) {
            return;
        }

        $presents = $ordonnancement->documents()->whereNotNull('type_piece')->pluck('type_piece')->all();
        $manquants = array_values(array_diff($requis, $presents));

        if ($manquants !== []) {
            throw ValidationException::withMessages([
                'documents' => __('Pièces obligatoires manquantes : :pieces.', ['pieces' => implode(', ', $manquants)]),
            ]);
        }
    }

    /**
     * Contrôle financier complet, ligne par ligne et global.
     *
     * @return array<string, bool>
     */
    public function controleFinancier(Ordonnancement $ordonnancement): array
    {
        $ordonnancement->loadMissing(['lignes.ligneBudgetaire', 'liquidation']);

        // Disponibilité budgétaire : montant ordonnancé ≤ liquidé sur chaque ligne budgétaire.
        $disponibilite = true;
        $coherenceImputations = true;
        foreach ($ordonnancement->lignes as $ligne) {
            if ($ligne->ligne_budgetaire_id === null) {
                $coherenceImputations = false;

                continue;
            }
            $lb = $ligne->ligneBudgetaire;
            if ($lb !== null) {
                $disponibleLiquide = bcsub((string) $lb->montant_liquide, (string) $lb->montant_ordonnance, self::SCALE);
                if (bccomp((string) $ligne->montant_ordonnance, $disponibleLiquide, self::SCALE) > 0) {
                    $disponibilite = false;
                }
            }
        }

        $total = $this->calcul->totalAOrdonnancer($ordonnancement);
        $conformiteBudgetaire = bccomp($total, $this->resteLiquidation($ordonnancement), self::SCALE) <= 0;
        $conformiteComptable = bccomp($total, '0', self::SCALE) > 0;
        $conformiteDocumentaire = $this->documentsConformes($ordonnancement);

        return [
            'disponibilite' => $disponibilite,
            'conformite_budgetaire' => $conformiteBudgetaire,
            'conformite_comptable' => $conformiteComptable,
            'coherence_imputations' => $coherenceImputations,
            'conformite_documentaire' => $conformiteDocumentaire,
        ];
    }

    public function assertControleFinancierOk(Ordonnancement $ordonnancement): void
    {
        $controles = $this->controleFinancier($ordonnancement);
        $echecs = array_keys(array_filter($controles, fn ($ok) => $ok === false));

        if ($echecs !== []) {
            throw ValidationException::withMessages([
                'controle_financier' => __('Contrôle financier en échec : :controles.', [
                    'controles' => implode(', ', $echecs),
                ]),
            ]);
        }
    }

    public function assertPretPourSoumission(Ordonnancement $ordonnancement): void
    {
        $this->assertLignesPresentes($ordonnancement);

        if (bccomp($this->calcul->totalAOrdonnancer($ordonnancement), '0', self::SCALE) <= 0) {
            throw ValidationException::withMessages([
                'montant' => __('Le montant à ordonnancer doit être strictement positif.'),
            ]);
        }

        $this->assertMontantDansLiquidation($ordonnancement);
    }

    public function assertPretPourMandat(Ordonnancement $ordonnancement): void
    {
        $this->assertControleFinancierOk($ordonnancement);
        $this->assertDocumentsObligatoires($ordonnancement);
        $this->assertMontantDansLiquidation($ordonnancement);
    }

    private function documentsConformes(Ordonnancement $ordonnancement): bool
    {
        $requis = (array) ($ordonnancement->type?->documents_obligatoires ?? []);
        if ($requis === []) {
            return true;
        }

        $presents = $ordonnancement->documents()->whereNotNull('type_piece')->pluck('type_piece')->all();

        return array_diff($requis, $presents) === [];
    }
}
