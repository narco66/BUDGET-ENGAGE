<?php

namespace App\Services;

use App\Enums\StatutEngagement;
use App\Models\Engagement;
use App\Models\Liquidation;
use App\Models\LigneBudgetaire;
use App\Models\Ordonnancement;
use App\Models\Paiement;
use App\Models\User;
use App\Support\BudgetJournal;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EngagementExecutionService
{
    public function recordLiquidation(Engagement $engagement, array $data, User $user): Liquidation
    {
        return DB::transaction(function () use ($engagement, $data, $user): Liquidation {
            Engagement::query()->whereKey($engagement->id)->lockForUpdate()->firstOrFail();
            $engagement->refresh();

            $this->assertEngagementAllowsExecutionRecording($engagement);

            $montant = $this->normalizeAmount($data['montant_liquide']);
            $sumExisting = $this->sumLiquidations($engagement);
            $totalAfter = bcadd($sumExisting, $montant, 2);

            if (bccomp($totalAfter, (string) $engagement->montant, 2) > 0) {
                throw ValidationException::withMessages([
                    'montant_liquide' => __('Le cumul des liquidations dépasse le montant de l’engagement (:max).', [
                        'max' => $engagement->montant,
                    ]),
                ]);
            }

            $ligne = LigneBudgetaire::query()
                ->whereKey($engagement->ligne_budgetaire_id)
                ->lockForUpdate()
                ->firstOrFail();

            $liquidation = Liquidation::query()->create([
                'engagement_id' => $engagement->id,
                'montant_liquide' => $montant,
                'date_liquidation' => $data['date_liquidation'],
                'reference' => $data['reference'] ?? null,
                'commentaire' => $data['commentaire'] ?? null,
                'created_by' => $user->id,
            ]);

            $ligne->update([
                'montant_liquide' => bcadd((string) $ligne->montant_liquide, $montant, 2),
            ]);

            $this->refreshExecutionStatut($engagement->fresh());

            $lid = $liquidation->id;
            $numeroEng = $engagement->numero;
            $mnt = (string) $liquidation->montant_liquide;

            DB::afterCommit(function () use ($user, $lid, $numeroEng, $mnt): void {
                BudgetJournal::record($user, __('journal.execution.liquidation', [
                    'id' => $lid,
                    'montant' => $mnt,
                    'numero' => $numeroEng,
                ]), Liquidation::find($lid), [
                    'liquidation_id' => $lid,
                    'montant' => $mnt,
                    'engagement_numero' => $numeroEng,
                ]);
            });

            return $liquidation;
        });
    }

    public function recordOrdonnancement(Liquidation $liquidation, array $data, User $user): Ordonnancement
    {
        return DB::transaction(function () use ($liquidation, $data, $user): Ordonnancement {
            Liquidation::query()->whereKey($liquidation->id)->lockForUpdate()->firstOrFail();
            $liquidation->load('engagement');
            $engagement = $liquidation->engagement;
            Engagement::query()->whereKey($engagement->id)->lockForUpdate()->firstOrFail();

            $this->assertEngagementAllowsExecutionRecording($engagement);

            $montant = $this->normalizeAmount($data['montant_ordonnance']);
            $sumOrd = $this->sumOrdonnancementsForLiquidation($liquidation);
            $remaining = bcsub((string) $liquidation->montant_liquide, $sumOrd, 2);

            if (bccomp($montant, $remaining, 2) > 0) {
                throw ValidationException::withMessages([
                    'montant_ordonnance' => __('Montant supérieur au reste liquidé disponible pour cette liquidation (:reste).', [
                        'reste' => $remaining,
                    ]),
                ]);
            }

            $ligne = LigneBudgetaire::query()
                ->whereKey($engagement->ligne_budgetaire_id)
                ->lockForUpdate()
                ->firstOrFail();

            $ordonnancement = Ordonnancement::query()->create([
                'liquidation_id' => $liquidation->id,
                'montant_ordonnance' => $montant,
                'date_ordonnancement' => $data['date_ordonnancement'],
                'reference' => $data['reference'] ?? null,
                'commentaire' => $data['commentaire'] ?? null,
                'created_by' => $user->id,
            ]);

            $ligne->update([
                'montant_ordonnance' => bcadd((string) $ligne->montant_ordonnance, $montant, 2),
            ]);

            $this->refreshExecutionStatut($engagement->fresh());

            $oid = $ordonnancement->id;
            $numeroEng = $engagement->numero;
            $mnt = (string) $ordonnancement->montant_ordonnance;

            DB::afterCommit(function () use ($user, $oid, $numeroEng, $mnt): void {
                BudgetJournal::record($user, __('journal.execution.ordonnancement', [
                    'id' => $oid,
                    'montant' => $mnt,
                    'numero' => $numeroEng,
                ]), Ordonnancement::find($oid), [
                    'ordonnancement_id' => $oid,
                    'montant' => $mnt,
                    'engagement_numero' => $numeroEng,
                ]);
            });

            return $ordonnancement;
        });
    }

    public function recordPaiement(Ordonnancement $ordonnancement, array $data, User $user): Paiement
    {
        return DB::transaction(function () use ($ordonnancement, $data, $user): Paiement {
            Ordonnancement::query()->whereKey($ordonnancement->id)->lockForUpdate()->firstOrFail();
            $ordonnancement->load('liquidation.engagement');
            $engagement = $ordonnancement->liquidation->engagement;
            Engagement::query()->whereKey($engagement->id)->lockForUpdate()->firstOrFail();

            $this->assertEngagementAllowsExecutionRecording($engagement);

            $montant = $this->normalizeAmount($data['montant_paye']);
            $sumPay = $this->sumPaiementsForOrdonnancement($ordonnancement);
            $remaining = bcsub((string) $ordonnancement->montant_ordonnance, $sumPay, 2);

            if (bccomp($montant, $remaining, 2) > 0) {
                throw ValidationException::withMessages([
                    'montant_paye' => __('Montant supérieur au reste ordonnancé disponible (:reste).', [
                        'reste' => $remaining,
                    ]),
                ]);
            }

            $ligne = LigneBudgetaire::query()
                ->whereKey($engagement->ligne_budgetaire_id)
                ->lockForUpdate()
                ->firstOrFail();

            $paiement = Paiement::query()->create([
                'ordonnancement_id' => $ordonnancement->id,
                'montant_paye' => $montant,
                'date_paiement' => $data['date_paiement'],
                'reference_paiement' => $data['reference_paiement'] ?? null,
                'mode_paiement' => $data['mode_paiement'] ?? null,
                'commentaire' => $data['commentaire'] ?? null,
                'created_by' => $user->id,
            ]);

            $ligne->update([
                'montant_paye' => bcadd((string) $ligne->montant_paye, $montant, 2),
            ]);

            $this->refreshExecutionStatut($engagement->fresh());

            $pid = $paiement->id;
            $numeroEng = $engagement->numero;
            $mnt = (string) $paiement->montant_paye;

            DB::afterCommit(function () use ($user, $pid, $numeroEng, $mnt): void {
                BudgetJournal::record($user, __('journal.execution.paiement', [
                    'id' => $pid,
                    'montant' => $mnt,
                    'numero' => $numeroEng,
                ]), Paiement::find($pid), [
                    'paiement_id' => $pid,
                    'montant' => $mnt,
                    'engagement_numero' => $numeroEng,
                ]);
            });

            return $paiement;
        });
    }

    public function refreshExecutionStatut(Engagement $engagement): void
    {
        $engagement->loadMissing(['liquidations.ordonnancements.paiements']);

        $montant = (string) $engagement->montant;
        if (bccomp($montant, '0', 2) <= 0) {
            return;
        }

        $totalLiq = $this->sumLiquidations($engagement);
        $totalOrd = $this->sumOrdonnancements($engagement);
        $totalPay = $this->sumPaiements($engagement);

        $statut = null;
        if (bccomp($totalPay, $montant, 2) >= 0) {
            $statut = StatutEngagement::Paye;
        } elseif (bccomp($totalOrd, $montant, 2) >= 0) {
            $statut = StatutEngagement::Ordonnance;
        } elseif (bccomp($totalLiq, $montant, 2) >= 0 || bccomp($totalLiq, '0', 2) > 0) {
            $statut = StatutEngagement::Liquide;
        }

        if ($statut === null) {
            return;
        }

        $rank = fn (StatutEngagement $s): int => match ($s) {
            StatutEngagement::Approuve => 10,
            StatutEngagement::Liquide => 20,
            StatutEngagement::Ordonnance => 30,
            StatutEngagement::Paye => 40,
            StatutEngagement::Cloture => 50,
            default => 0,
        };

        $currentRank = $rank($engagement->statut);
        $newRank = $rank($statut);

        if ($newRank >= $currentRank && $engagement->statut !== $statut) {
            $engagement->update(['statut' => $statut]);
        }
    }

    protected function assertEngagementAllowsExecutionRecording(Engagement $engagement): void
    {
        if (in_array($engagement->statut, [
            StatutEngagement::Annule,
            StatutEngagement::Rejete,
            StatutEngagement::Brouillon,
            StatutEngagement::Soumis,
            StatutEngagement::EnValidation,
            StatutEngagement::Retourne,
            StatutEngagement::Paye,
            StatutEngagement::Cloture,
        ], true)) {
            throw ValidationException::withMessages([
                'engagement' => __('L’engagement ne permet pas cette opération d’exécution au statut actuel.'),
            ]);
        }
    }

    protected function sumLiquidations(Engagement $engagement): string
    {
        $sum = $engagement->liquidations()->sum('montant_liquide');

        return $this->normalizeAmount($sum);
    }

    protected function sumOrdonnancements(Engagement $engagement): string
    {
        $total = '0';
        foreach ($engagement->liquidations as $liq) {
            foreach ($liq->ordonnancements as $ord) {
                $total = bcadd($total, (string) $ord->montant_ordonnance, 2);
            }
        }

        return $total;
    }

    protected function sumOrdonnancementsForLiquidation(Liquidation $liquidation): string
    {
        $sum = $liquidation->ordonnancements()->sum('montant_ordonnance');

        return $this->normalizeAmount($sum);
    }

    protected function sumPaiements(Engagement $engagement): string
    {
        $total = '0';
        foreach ($engagement->liquidations as $liq) {
            foreach ($liq->ordonnancements as $ord) {
                foreach ($ord->paiements as $pay) {
                    $total = bcadd($total, (string) $pay->montant_paye, 2);
                }
            }
        }

        return $total;
    }

    protected function sumPaiementsForOrdonnancement(Ordonnancement $ordonnancement): string
    {
        $sum = $ordonnancement->paiements()->sum('montant_paye');

        return $this->normalizeAmount($sum);
    }

    protected function normalizeAmount(mixed $value): string
    {
        if (is_numeric($value)) {
            return number_format((float) $value, 2, '.', '');
        }

        return number_format((float) (string) $value, 2, '.', '');
    }
}
