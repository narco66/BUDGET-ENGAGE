<?php

namespace App\Services;

use App\Enums\StatutEngagement;
use App\Events\EngagementStatusChanged;
use App\Models\Engagement;
use App\Models\EngagementImputation;
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
            $imputation = $this->resolveLiquidationImputation($engagement, $data);
            $sumExisting = $this->sumLiquidationsForImputation($imputation);
            $remaining = bcsub((string) $imputation->montant_impute, $sumExisting, 2);

            if (bccomp($montant, $remaining, 2) > 0) {
                throw ValidationException::withMessages([
                    'montant_liquide' => __('Le cumul des liquidations dépasse le montant de l’engagement (:max).', [
                        'max' => $remaining,
                    ]),
                ]);
            }

            $ligne = LigneBudgetaire::query()
                ->whereKey($imputation->ligne_budgetaire_id)
                ->lockForUpdate()
                ->firstOrFail();

            $liquidation = Liquidation::query()->create([
                'engagement_id' => $engagement->id,
                'engagement_imputation_id' => $imputation->id,
                'montant_liquide' => $montant,
                'date_liquidation' => $data['date_liquidation'],
                'reference' => $data['reference'] ?? null,
                'commentaire' => $data['commentaire'] ?? null,
                'created_by' => $user->id,
            ]);

            $ligne->update([
                'montant_liquide' => bcadd((string) $ligne->montant_liquide, $montant, 2),
            ]);

            $this->recalculateExecutionStatut($engagement->fresh(), $user);

            $lid = $liquidation->id;
            $numeroEng = $engagement->numero;
            $mnt = (string) $liquidation->montant_liquide;

            DB::afterCommit(function () use ($user, $lid, $numeroEng, $mnt): void {
                $liquidation = Liquidation::with('engagementImputation.ligneBudgetaire')->find($lid);
                BudgetJournal::record($user, __('journal.execution.liquidation', [
                    'id' => $lid,
                    'montant' => $mnt,
                    'numero' => $numeroEng,
                ]), Liquidation::find($lid), [
                    'liquidation_id' => $lid,
                    'montant' => $mnt,
                    'engagement_numero' => $numeroEng,
                    'engagement_imputation_id' => $liquidation?->engagement_imputation_id,
                    'ligne_budgetaire_id' => $liquidation?->engagementImputation?->ligne_budgetaire_id,
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

            $imputation = $this->resolveImputationFromLiquidation($liquidation, $engagement);
            $ligne = LigneBudgetaire::query()
                ->whereKey($imputation->ligne_budgetaire_id)
                ->lockForUpdate()
                ->firstOrFail();

            $ordonnancement = Ordonnancement::query()->create([
                'liquidation_id' => $liquidation->id,
                'engagement_imputation_id' => $imputation->id,
                'montant_ordonnance' => $montant,
                'date_ordonnancement' => $data['date_ordonnancement'],
                'reference' => $data['reference'] ?? null,
                'commentaire' => $data['commentaire'] ?? null,
                'created_by' => $user->id,
            ]);

            $ligne->update([
                'montant_ordonnance' => bcadd((string) $ligne->montant_ordonnance, $montant, 2),
            ]);

            $this->recalculateExecutionStatut($engagement->fresh(), $user);

            $oid = $ordonnancement->id;
            $numeroEng = $engagement->numero;
            $mnt = (string) $ordonnancement->montant_ordonnance;

            DB::afterCommit(function () use ($user, $oid, $numeroEng, $mnt): void {
                $ordonnancement = Ordonnancement::with('engagementImputation.ligneBudgetaire')->find($oid);
                BudgetJournal::record($user, __('journal.execution.ordonnancement', [
                    'id' => $oid,
                    'montant' => $mnt,
                    'numero' => $numeroEng,
                ]), Ordonnancement::find($oid), [
                    'ordonnancement_id' => $oid,
                    'montant' => $mnt,
                    'engagement_numero' => $numeroEng,
                    'engagement_imputation_id' => $ordonnancement?->engagement_imputation_id,
                    'ligne_budgetaire_id' => $ordonnancement?->engagementImputation?->ligne_budgetaire_id,
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

            $imputation = $this->resolveImputationFromOrdonnancement($ordonnancement, $engagement);
            $ligne = LigneBudgetaire::query()
                ->whereKey($imputation->ligne_budgetaire_id)
                ->lockForUpdate()
                ->firstOrFail();

            $paiement = Paiement::query()->create([
                'ordonnancement_id' => $ordonnancement->id,
                'engagement_imputation_id' => $imputation->id,
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

            $this->recalculateExecutionStatut($engagement->fresh(), $user);

            $pid = $paiement->id;
            $numeroEng = $engagement->numero;
            $mnt = (string) $paiement->montant_paye;

            DB::afterCommit(function () use ($user, $pid, $numeroEng, $mnt): void {
                $paiement = Paiement::with('engagementImputation.ligneBudgetaire')->find($pid);
                BudgetJournal::record($user, __('journal.execution.paiement', [
                    'id' => $pid,
                    'montant' => $mnt,
                    'numero' => $numeroEng,
                ]), Paiement::find($pid), [
                    'paiement_id' => $pid,
                    'montant' => $mnt,
                    'engagement_numero' => $numeroEng,
                    'engagement_imputation_id' => $paiement?->engagement_imputation_id,
                    'ligne_budgetaire_id' => $paiement?->engagementImputation?->ligne_budgetaire_id,
                ]);
            });

            return $paiement;
        });
    }

    public function updateLiquidation(Liquidation $liquidation, array $data, User $user): Liquidation
    {
        return DB::transaction(function () use ($liquidation, $data, $user): Liquidation {
            $liquidation = Liquidation::query()->whereKey($liquidation->id)->lockForUpdate()->firstOrFail();
            $engagement = Engagement::query()->whereKey($liquidation->engagement_id)->lockForUpdate()->firstOrFail();

            if ($liquidation->ordonnancements()->exists()) {
                throw ValidationException::withMessages([
                    'liquidation' => __('Impossible de modifier une liquidation ayant des ordonnancements.'),
                ]);
            }

            $this->assertEngagementAllowsExecutionRecording($engagement);

            $newMontant = $this->normalizeAmount($data['montant_liquide']);
            $oldMontant = $this->normalizeAmount($liquidation->montant_liquide);
            $imputation = $this->resolveImputationFromLiquidation($liquidation, $engagement);
            $sumOthers = bcsub($this->sumLiquidationsForImputation($imputation), $oldMontant, 2);
            $remaining = bcsub((string) $imputation->montant_impute, $sumOthers, 2);

            if (bccomp($newMontant, $remaining, 2) > 0) {
                throw ValidationException::withMessages([
                    'montant_liquide' => __('Le montant dépasse le reste imputable (:max).', ['max' => $remaining]),
                ]);
            }

            $ligne = LigneBudgetaire::query()->whereKey($imputation->ligne_budgetaire_id)->lockForUpdate()->firstOrFail();
            $delta = bcsub($newMontant, $oldMontant, 2);
            $ligne->update([
                'montant_liquide' => bcadd((string) $ligne->montant_liquide, $delta, 2),
            ]);

            $liquidation->update([
                'montant_liquide' => $newMontant,
                'date_liquidation' => $data['date_liquidation'],
                'reference' => $data['reference'] ?? null,
                'commentaire' => $data['commentaire'] ?? null,
            ]);

            $this->recalculateExecutionStatut($engagement->fresh(), $user);

            return $liquidation->fresh();
        });
    }

    public function cancelLiquidation(Liquidation $liquidation, User $user, ?string $motif = null): void
    {
        DB::transaction(function () use ($liquidation, $user, $motif): void {
            $liquidation = Liquidation::query()->whereKey($liquidation->id)->lockForUpdate()->firstOrFail();
            $engagement = Engagement::query()->whereKey($liquidation->engagement_id)->lockForUpdate()->firstOrFail();

            if ($engagement->statut === StatutEngagement::Cloture) {
                throw ValidationException::withMessages(['liquidation' => __('Engagement clôturé : régularisation impossible.')]);
            }

            if ($liquidation->ordonnancements()->exists()) {
                throw ValidationException::withMessages([
                    'liquidation' => __('Annulez d’abord les ordonnancements rattachés à cette liquidation.'),
                ]);
            }

            $imputation = $this->resolveImputationFromLiquidation($liquidation, $engagement);
            $ligne = LigneBudgetaire::query()->whereKey($imputation->ligne_budgetaire_id)->lockForUpdate()->firstOrFail();
            $montant = $this->normalizeAmount($liquidation->montant_liquide);

            $ligne->update([
                'montant_liquide' => bcsub((string) $ligne->montant_liquide, $montant, 2),
            ]);

            $liquidation->update([
                'cancelled_at' => now(),
                'cancelled_by' => $user->id,
                'motif_annulation' => $motif,
            ]);
            $liquidation->delete();

            $this->journalCancellation($user, 'liquidation', $liquidation->id, $montant, $engagement->numero);
            $this->recalculateExecutionStatut($engagement->fresh(), $user);
        });
    }

    public function updateOrdonnancement(Ordonnancement $ordonnancement, array $data, User $user): Ordonnancement
    {
        return DB::transaction(function () use ($ordonnancement, $data, $user): Ordonnancement {
            $ordonnancement = Ordonnancement::query()->whereKey($ordonnancement->id)->lockForUpdate()->firstOrFail();
            $liquidation = Liquidation::query()->whereKey($ordonnancement->liquidation_id)->lockForUpdate()->firstOrFail();
            $engagement = Engagement::query()->whereKey($liquidation->engagement_id)->lockForUpdate()->firstOrFail();

            if ($ordonnancement->paiements()->exists()) {
                throw ValidationException::withMessages([
                    'ordonnancement' => __('Impossible de modifier un ordonnancement ayant des paiements.'),
                ]);
            }

            $this->assertEngagementAllowsExecutionRecording($engagement);

            $newMontant = $this->normalizeAmount($data['montant_ordonnance']);
            $oldMontant = $this->normalizeAmount($ordonnancement->montant_ordonnance);
            $sumOthers = bcsub($this->sumOrdonnancementsForLiquidation($liquidation), $oldMontant, 2);
            $remaining = bcsub((string) $liquidation->montant_liquide, $sumOthers, 2);

            if (bccomp($newMontant, $remaining, 2) > 0) {
                throw ValidationException::withMessages([
                    'montant_ordonnance' => __('Montant supérieur au reste liquidé disponible (:reste).', ['reste' => $remaining]),
                ]);
            }

            $imputation = $this->resolveImputationFromOrdonnancement($ordonnancement, $engagement);
            $ligne = LigneBudgetaire::query()->whereKey($imputation->ligne_budgetaire_id)->lockForUpdate()->firstOrFail();
            $delta = bcsub($newMontant, $oldMontant, 2);
            $ligne->update([
                'montant_ordonnance' => bcadd((string) $ligne->montant_ordonnance, $delta, 2),
            ]);

            $ordonnancement->update([
                'montant_ordonnance' => $newMontant,
                'date_ordonnancement' => $data['date_ordonnancement'],
                'reference' => $data['reference'] ?? null,
                'commentaire' => $data['commentaire'] ?? null,
            ]);

            $this->recalculateExecutionStatut($engagement->fresh(), $user);

            return $ordonnancement->fresh();
        });
    }

    public function cancelOrdonnancement(Ordonnancement $ordonnancement, User $user, ?string $motif = null): void
    {
        DB::transaction(function () use ($ordonnancement, $user, $motif): void {
            $ordonnancement = Ordonnancement::query()->whereKey($ordonnancement->id)->lockForUpdate()->firstOrFail();
            $liquidation = Liquidation::query()->whereKey($ordonnancement->liquidation_id)->lockForUpdate()->firstOrFail();
            $engagement = Engagement::query()->whereKey($liquidation->engagement_id)->lockForUpdate()->firstOrFail();

            if ($engagement->statut === StatutEngagement::Cloture) {
                throw ValidationException::withMessages(['ordonnancement' => __('Engagement clôturé : régularisation impossible.')]);
            }

            if ($ordonnancement->paiements()->exists()) {
                throw ValidationException::withMessages([
                    'ordonnancement' => __('Annulez d’abord les paiements rattachés à cet ordonnancement.'),
                ]);
            }

            $imputation = $this->resolveImputationFromOrdonnancement($ordonnancement, $engagement);
            $ligne = LigneBudgetaire::query()->whereKey($imputation->ligne_budgetaire_id)->lockForUpdate()->firstOrFail();
            $montant = $this->normalizeAmount($ordonnancement->montant_ordonnance);

            $ligne->update([
                'montant_ordonnance' => bcsub((string) $ligne->montant_ordonnance, $montant, 2),
            ]);

            $ordonnancement->update([
                'cancelled_at' => now(),
                'cancelled_by' => $user->id,
                'motif_annulation' => $motif,
            ]);
            $ordonnancement->delete();

            $this->journalCancellation($user, 'ordonnancement', $ordonnancement->id, $montant, $engagement->numero);
            $this->recalculateExecutionStatut($engagement->fresh(), $user);
        });
    }

    public function updatePaiement(Paiement $paiement, array $data, User $user): Paiement
    {
        return DB::transaction(function () use ($paiement, $data, $user): Paiement {
            $paiement = Paiement::query()->whereKey($paiement->id)->lockForUpdate()->firstOrFail();
            $ordonnancement = Ordonnancement::query()->whereKey($paiement->ordonnancement_id)->lockForUpdate()->firstOrFail();
            $liquidation = Liquidation::query()->whereKey($ordonnancement->liquidation_id)->lockForUpdate()->firstOrFail();
            $engagement = Engagement::query()->whereKey($liquidation->engagement_id)->lockForUpdate()->firstOrFail();

            $this->assertEngagementAllowsExecutionRecording($engagement);

            $newMontant = $this->normalizeAmount($data['montant_paye']);
            $oldMontant = $this->normalizeAmount($paiement->montant_paye);
            $sumOthers = bcsub($this->sumPaiementsForOrdonnancement($ordonnancement), $oldMontant, 2);
            $remaining = bcsub((string) $ordonnancement->montant_ordonnance, $sumOthers, 2);

            if (bccomp($newMontant, $remaining, 2) > 0) {
                throw ValidationException::withMessages([
                    'montant_paye' => __('Montant supérieur au reste ordonnancé disponible (:reste).', ['reste' => $remaining]),
                ]);
            }

            $imputation = $this->resolveImputationFromOrdonnancement($ordonnancement, $engagement);
            $ligne = LigneBudgetaire::query()->whereKey($imputation->ligne_budgetaire_id)->lockForUpdate()->firstOrFail();
            $delta = bcsub($newMontant, $oldMontant, 2);
            $ligne->update([
                'montant_paye' => bcadd((string) $ligne->montant_paye, $delta, 2),
            ]);

            $paiement->update([
                'montant_paye' => $newMontant,
                'date_paiement' => $data['date_paiement'],
                'reference_paiement' => $data['reference_paiement'] ?? null,
                'mode_paiement' => $data['mode_paiement'] ?? null,
                'commentaire' => $data['commentaire'] ?? null,
            ]);

            $this->recalculateExecutionStatut($engagement->fresh(), $user);

            return $paiement->fresh();
        });
    }

    public function cancelPaiement(Paiement $paiement, User $user, ?string $motif = null): void
    {
        DB::transaction(function () use ($paiement, $user, $motif): void {
            $paiement = Paiement::query()->whereKey($paiement->id)->lockForUpdate()->firstOrFail();
            $ordonnancement = Ordonnancement::query()->whereKey($paiement->ordonnancement_id)->lockForUpdate()->firstOrFail();
            $liquidation = Liquidation::query()->whereKey($ordonnancement->liquidation_id)->lockForUpdate()->firstOrFail();
            $engagement = Engagement::query()->whereKey($liquidation->engagement_id)->lockForUpdate()->firstOrFail();

            if ($engagement->statut === StatutEngagement::Cloture) {
                throw ValidationException::withMessages(['paiement' => __('Engagement clôturé : régularisation impossible.')]);
            }

            $imputation = $this->resolveImputationFromOrdonnancement($ordonnancement, $engagement);
            $ligne = LigneBudgetaire::query()->whereKey($imputation->ligne_budgetaire_id)->lockForUpdate()->firstOrFail();
            $montant = $this->normalizeAmount($paiement->montant_paye);

            $ligne->update([
                'montant_paye' => bcsub((string) $ligne->montant_paye, $montant, 2),
            ]);

            $paiement->update([
                'cancelled_at' => now(),
                'cancelled_by' => $user->id,
                'motif_annulation' => $motif,
            ]);
            $paiement->delete();

            $this->journalCancellation($user, 'paiement', $paiement->id, $montant, $engagement->numero);
            $this->recalculateExecutionStatut($engagement->fresh(), $user);
        });
    }

    /**
     * Recalcule le statut d'exécution (montée et descente) à partir des cumuls réels.
     */
    public function recalculateExecutionStatut(Engagement $engagement, ?User $user = null): void
    {
        $engagement->loadMissing(['liquidations.ordonnancements.paiements']);

        $montant = (string) $engagement->montant;
        if (bccomp($montant, '0', 2) <= 0) {
            return;
        }

        if ($engagement->statut === StatutEngagement::Cloture) {
            return;
        }

        $executionStates = [
            StatutEngagement::Approuve,
            StatutEngagement::Liquide,
            StatutEngagement::Ordonnance,
            StatutEngagement::Paye,
        ];

        if (! in_array($engagement->statut, $executionStates, true)) {
            return;
        }

        $totalLiq = $this->sumLiquidations($engagement);
        $totalOrd = $this->sumOrdonnancements($engagement);
        $totalPay = $this->sumPaiements($engagement);

        $newStatut = StatutEngagement::Approuve;
        if (bccomp($totalPay, $montant, 2) >= 0) {
            $newStatut = StatutEngagement::Paye;
        } elseif (bccomp($totalOrd, $montant, 2) >= 0) {
            $newStatut = StatutEngagement::Ordonnance;
        } elseif (bccomp($totalLiq, $montant, 2) >= 0) {
            $newStatut = StatutEngagement::Liquide;
        }

        if ($engagement->statut !== $newStatut) {
            $fromStatus = $engagement->statut;
            $engagement->update(['statut' => $newStatut]);

            DB::afterCommit(function () use ($engagement, $fromStatus, $newStatut, $user): void {
                event(new EngagementStatusChanged(
                    $engagement->id,
                    $fromStatus->value,
                    $newStatut->value,
                    $user?->id
                ));
            });
        }
    }

    /** @deprecated Utiliser recalculateExecutionStatut() */
    public function refreshExecutionStatut(Engagement $engagement, ?User $user = null): void
    {
        $this->recalculateExecutionStatut($engagement, $user);
    }

    protected function journalCancellation(User $user, string $type, int $id, string $montant, string $numeroEng): void
    {
        $lid = $id;
        $mnt = $montant;
        $numero = $numeroEng;

        DB::afterCommit(function () use ($user, $type, $lid, $mnt, $numero): void {
            BudgetJournal::record($user, __('journal.execution.cancelled_'.$type, [
                'id' => $lid,
                'montant' => $mnt,
                'numero' => $numero,
            ]), null, [
                'type' => $type,
                'id' => $lid,
                'montant' => $mnt,
                'engagement_numero' => $numero,
            ]);
        });
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

    protected function sumLiquidationsForImputation(EngagementImputation $imputation): string
    {
        $sum = Liquidation::query()
            ->where('engagement_imputation_id', $imputation->id)
            ->sum('montant_liquide');

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

    protected function resolveLiquidationImputation(Engagement $engagement, array $data): EngagementImputation
    {
        if (! $engagement->imputations()->exists()) {
            return $engagement->imputations()->create([
                'ligne_budgetaire_id' => $engagement->ligne_budgetaire_id,
                'montant_impute' => (string) $engagement->montant,
                'observation' => 'Imputation générée automatiquement (compatibilité historique).',
            ]);
        }

        $imputationId = (int) ($data['engagement_imputation_id'] ?? 0);
        $imputation = $engagement->imputations()->whereKey($imputationId)->lockForUpdate()->first();

        if ($imputation !== null) {
            return $imputation;
        }

        $fallback = $engagement->imputations()
            ->where('ligne_budgetaire_id', $engagement->ligne_budgetaire_id)
            ->first();

        if ($fallback !== null) {
            return $fallback;
        }

        throw ValidationException::withMessages([
            'engagement_imputation_id' => __('Imputation introuvable pour cet engagement.'),
        ]);
    }

    protected function resolveImputationFromLiquidation(Liquidation $liquidation, Engagement $engagement): EngagementImputation
    {
        if ($liquidation->engagement_imputation_id !== null) {
            $imputation = $engagement->imputations()->whereKey($liquidation->engagement_imputation_id)->first();
            if ($imputation !== null) {
                return $imputation;
            }
        }

        $fallback = $engagement->imputations()
            ->where('ligne_budgetaire_id', $engagement->ligne_budgetaire_id)
            ->first();

        if ($fallback !== null) {
            return $fallback;
        }

        throw ValidationException::withMessages([
            'engagement' => __('Impossible de résoudre l’imputation liée à cette liquidation.'),
        ]);
    }

    protected function resolveImputationFromOrdonnancement(Ordonnancement $ordonnancement, Engagement $engagement): EngagementImputation
    {
        if ($ordonnancement->engagement_imputation_id !== null) {
            $imputation = $engagement->imputations()->whereKey($ordonnancement->engagement_imputation_id)->first();
            if ($imputation !== null) {
                return $imputation;
            }
        }

        return $this->resolveImputationFromLiquidation($ordonnancement->liquidation, $engagement);
    }
}
