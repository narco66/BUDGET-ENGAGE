<?php

namespace App\Services\Liquidation;

use App\Models\Liquidation;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Contrôles métier de cohérence de la liquidation : plafond engagement,
 * service fait, pièces obligatoires, présence de lignes. Invoqué avant
 * soumission au circuit et avant certification.
 */
class LiquidationValidationService
{
    private const SCALE = 2;

    public function __construct(private readonly LiquidationServiceFaitService $serviceFait)
    {
    }

    /**
     * Cumul net déjà liquidé (certifié) sur l'engagement, hors liquidation courante.
     */
    public function montantDejaLiquide(Liquidation $liquidation): string
    {
        return (string) Liquidation::query()
            ->where('engagement_id', $liquidation->engagement_id)
            ->whereKeyNot($liquidation->id)
            ->whereNull('cancelled_at')
            ->sum('montant_liquide');
    }

    public function resteALiquider(Liquidation $liquidation): string
    {
        $liquidation->loadMissing('engagement');
        $montantEngagement = (string) ($liquidation->engagement?->montant ?? '0');

        return bcsub($montantEngagement, $this->montantDejaLiquide($liquidation), self::SCALE);
    }

    public function assertMontantDansEngagement(Liquidation $liquidation): void
    {
        $reste = $this->resteALiquider($liquidation);

        if (bccomp((string) $liquidation->montant_net, $reste, self::SCALE) > 0) {
            throw ValidationException::withMessages([
                'montant_net' => __('Le montant net (:net) dépasse le reste à liquider sur l’engagement (:reste).', [
                    'net' => $liquidation->montant_net,
                    'reste' => $reste,
                ]),
            ]);
        }
    }

    public function assertLignesPresentes(Liquidation $liquidation): void
    {
        if ($liquidation->lignes()->count() === 0) {
            throw ValidationException::withMessages([
                'lignes' => __('La liquidation doit comporter au moins une ligne de prestation.'),
            ]);
        }
    }

    /**
     * Vérifie la présence des pièces déclarées obligatoires par le type.
     */
    public function assertDocumentsObligatoires(Liquidation $liquidation): void
    {
        $liquidation->loadMissing('type');
        $requis = (array) ($liquidation->type?->documents_obligatoires ?? []);

        if ($requis === []) {
            return;
        }

        $presents = $liquidation->documents()
            ->whereNotNull('type_piece')
            ->pluck('type_piece')
            ->all();

        $manquants = array_values(array_diff($requis, $presents));

        if ($manquants !== []) {
            throw ValidationException::withMessages([
                'documents' => __('Pièces obligatoires manquantes : :pieces.', [
                    'pieces' => implode(', ', $manquants),
                ]),
            ]);
        }
    }

    /**
     * Garde-fous avant soumission au circuit de validation.
     */
    public function assertPretPourSoumission(Liquidation $liquidation): void
    {
        $this->assertLignesPresentes($liquidation);

        if (bccomp((string) $liquidation->montant_net, '0', self::SCALE) <= 0) {
            throw ValidationException::withMessages([
                'montant_net' => __('Le montant net doit être strictement positif.'),
            ]);
        }

        $this->serviceFait->assertServiceFaitValide($liquidation);
        $this->assertMontantDansEngagement($liquidation);
    }

    /**
     * Garde-fous avant certification (état exigible définitif).
     */
    public function assertPretPourCertification(Liquidation $liquidation): void
    {
        $this->serviceFait->assertServiceFaitValide($liquidation);
        $this->assertDocumentsObligatoires($liquidation);
        $this->assertMontantDansEngagement($liquidation);
    }

    /**
     * Verrou de concurrence sur l'engagement + recalcul du reste sous lock.
     */
    public function assertMontantDansEngagementVerrouille(Liquidation $liquidation): void
    {
        DB::transaction(function () use ($liquidation): void {
            Liquidation::query()->whereKey($liquidation->id)->lockForUpdate()->firstOrFail();
            $this->assertMontantDansEngagement($liquidation);
        });
    }
}
