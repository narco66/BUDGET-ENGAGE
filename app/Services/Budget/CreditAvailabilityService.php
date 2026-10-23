<?php

namespace App\Services\Budget;

use App\Enums\StatutEngagement;
use App\Models\Engagement;
use App\Models\EngagementImputation;
use App\Models\ExpressionBesoin;
use App\Models\LigneBudgetaire;
use Illuminate\Validation\ValidationException;

/**
 * Controle de disponibilite des credits a l'engagement.
 *
 * Disponible previsionnel = plafond (revise/vote) - engage (engagements approuves)
 *                           - reservations (engagements en cours, non encore approuves).
 *
 * Empeche qu'un engagement (ou un cumul d'engagements en circuit) ne depasse les credits ouverts
 * de la ligne budgetaire (notamment les lignes generees depuis le Budget).
 */
class CreditAvailabilityService
{
    /** Statuts reservant des credits sans engagement comptable definitif. */
    private const RESERVING = [
        StatutEngagement::Brouillon,
        StatutEngagement::Soumis,
        StatutEngagement::EnValidation,
        StatutEngagement::Retourne,
    ];

    public function plafond(LigneBudgetaire $ligne): string
    {
        $revise = (float) $ligne->montant_revise;
        $valeur = $revise > 0 ? $ligne->montant_revise : $ligne->montant_vote;

        return number_format((float) $valeur, 2, '.', '');
    }

    public function engage(LigneBudgetaire $ligne): string
    {
        return number_format((float) $ligne->montant_engage, 2, '.', '');
    }

    /**
     * Reservations en cours sur la ligne (engagements non approuves), hors engagement exclu.
     */
    public function reserve(LigneBudgetaire $ligne, ?int $excludeEngagementId = null): string
    {
        $statuts = array_map(fn (StatutEngagement $s) => $s->value, self::RESERVING);

        $sum = EngagementImputation::query()
            ->where('ligne_budgetaire_id', $ligne->id)
            ->whereHas('engagement', function ($q) use ($statuts, $excludeEngagementId): void {
                $q->whereIn('statut', $statuts);
                if ($excludeEngagementId !== null) {
                    $q->where('id', '!=', $excludeEngagementId);
                }
            })
            ->sum('montant_impute');

        return number_format((float) $sum, 2, '.', '');
    }

    /**
     * Disponible previsionnel (plafond - engage - reservations).
     */
    public function disponible(LigneBudgetaire $ligne, ?int $excludeEngagementId = null): string
    {
        $base = bcsub($this->plafond($ligne), $this->engage($ligne), 2);

        return bcsub($base, $this->reserve($ligne, $excludeEngagementId), 2);
    }

    /**
     * Disponible comptable (plafond - engage, sans reservations).
     */
    public function disponibleComptable(LigneBudgetaire $ligne): string
    {
        return bcsub($this->plafond($ligne), $this->engage($ligne), 2);
    }

    /**
     * Carte des disponibles previsionnels par ligne (1 requete groupee pour les reservations).
     *
     * @param  iterable<int, LigneBudgetaire>  $lignes
     * @return array<int, float>
     */
    public function disponibleMap(iterable $lignes, ?int $excludeEngagementId = null): array
    {
        $lignes = collect($lignes);
        $ids = $lignes->pluck('id')->all();
        if ($ids === []) {
            return [];
        }

        $statuts = array_map(fn (StatutEngagement $s) => $s->value, self::RESERVING);
        $reserves = EngagementImputation::query()
            ->whereIn('ligne_budgetaire_id', $ids)
            ->whereHas('engagement', function ($q) use ($statuts, $excludeEngagementId): void {
                $q->whereIn('statut', $statuts);
                if ($excludeEngagementId !== null) {
                    $q->where('id', '!=', $excludeEngagementId);
                }
            })
            ->selectRaw('ligne_budgetaire_id, SUM(montant_impute) as s')
            ->groupBy('ligne_budgetaire_id')
            ->pluck('s', 'ligne_budgetaire_id');

        $map = [];
        foreach ($lignes as $ligne) {
            $plafond = (float) $ligne->montant_revise > 0 ? (float) $ligne->montant_revise : (float) $ligne->montant_vote;
            $map[$ligne->id] = $plafond - (float) $ligne->montant_engage - (float) ($reserves[$ligne->id] ?? 0);
        }

        return $map;
    }

    /**
     * Verifie qu'une liste d'imputations respecte le disponible previsionnel.
     * Leve une ValidationException sur la premiere imputation en depassement.
     *
     * @param  array<int, array{ligne_budgetaire_id:int, montant_impute:string|float}>  $imputations
     */
    public function assertImputations(array $imputations, ?int $excludeEngagementId = null): void
    {
        foreach ($imputations as $index => $imputation) {
            $ligne = LigneBudgetaire::query()->findOrFail((int) $imputation['ligne_budgetaire_id']);
            $disponible = $this->disponible($ligne, $excludeEngagementId);

            if (bccomp(number_format((float) $imputation['montant_impute'], 2, '.', ''), $disponible, 2) > 0) {
                throw ValidationException::withMessages([
                    "imputations.{$index}.montant_impute" => __('Le montant imputé (:m) dépasse le crédit disponible de la ligne :code (disponible prévisionnel : :d).', [
                        'm' => number_format((float) $imputation['montant_impute'], 0, ',', ' '),
                        'code' => $ligne->code,
                        'd' => number_format((float) $disponible, 0, ',', ' '),
                    ]),
                ]);
            }
        }
    }

    /**
     * Verifie qu'un engagement existant (ses imputations) respecte le disponible previsionnel,
     * en s'excluant lui-meme des reservations. Utilise au moment de la soumission.
     */
    public function assertEngagement(Engagement $engagement): void
    {
        $imputations = $engagement->imputations()->get(['ligne_budgetaire_id', 'montant_impute']);

        if ($imputations->isEmpty() && $engagement->ligne_budgetaire_id !== null) {
            $imputations = collect([(object) [
                'ligne_budgetaire_id' => $engagement->ligne_budgetaire_id,
                'montant_impute' => $engagement->montant,
            ]]);
        }

        foreach ($imputations as $imputation) {
            $ligne = LigneBudgetaire::query()->find($imputation->ligne_budgetaire_id);
            if ($ligne === null) {
                continue;
            }
            $disponible = $this->disponible($ligne, $engagement->id);

            if (bccomp(number_format((float) $imputation->montant_impute, 2, '.', ''), $disponible, 2) > 0) {
                throw ValidationException::withMessages([
                    'imputations' => __('Crédit insuffisant sur la ligne :code : imputé :m, disponible prévisionnel :d.', [
                        'code' => $ligne->code,
                        'm' => number_format((float) $imputation->montant_impute, 0, ',', ' '),
                        'd' => number_format((float) $disponible, 0, ',', ' '),
                    ]),
                ]);
            }
        }
    }

    /**
     * Vérifie que les lignes budgétaires d'un besoin disposent de crédits suffisants.
     */
    public function assertBesoinLignes(ExpressionBesoin $besoin): void
    {
        $besoin->loadMissing('lignes');

        $aggregated = [];
        foreach ($besoin->lignes as $ligneBesoin) {
            if ($ligneBesoin->ligne_budgetaire_id === null) {
                continue;
            }
            $id = (int) $ligneBesoin->ligne_budgetaire_id;
            $aggregated[$id] = bcadd(
                $aggregated[$id] ?? '0',
                number_format((float) $ligneBesoin->montant, 2, '.', ''),
                2
            );
        }

        foreach ($aggregated as $ligneId => $montant) {
            $ligne = LigneBudgetaire::query()->findOrFail($ligneId);
            $disponible = $this->disponible($ligne);

            if (bccomp($montant, $disponible, 2) > 0) {
                throw ValidationException::withMessages([
                    'workflow' => __('Crédit insuffisant sur la ligne :code pour ce besoin : imputé :m, disponible prévisionnel :d.', [
                        'code' => $ligne->code,
                        'm' => number_format((float) $montant, 0, ',', ' '),
                        'd' => number_format((float) $disponible, 0, ',', ' '),
                    ]),
                ]);
            }
        }

        if ($aggregated === []) {
            throw ValidationException::withMessages([
                'workflow' => __('Aucune ligne budgétaire n’est renseignée sur ce besoin.'),
            ]);
        }
    }
}
