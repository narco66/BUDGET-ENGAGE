<?php

namespace App\Services;

use App\Enums\DecisionWorkflow;
use App\Enums\StatutEngagement;
use App\Events\EngagementApproved;
use App\Events\EngagementStatusChanged;
use App\Models\Engagement;
use App\Models\LigneBudgetaire;
use App\Models\ParametreControle;
use App\Models\User;
use App\Models\ValidationWorkflow;
use App\Support\BudgetJournal;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EngagementWorkflowService
{
    public function submitForValidation(Engagement $engagement, User $user): void
    {
        $definitions = config('engagement_workflow.steps', []);

        if ($definitions === []) {
            throw ValidationException::withMessages([
                'workflow' => __('Aucune étape de validation n’est configurée.'),
            ]);
        }

        DB::transaction(function () use ($engagement, $user): void {
            Engagement::query()->whereKey($engagement->id)->lockForUpdate()->firstOrFail();
            $fromStatus = $engagement->statut;

            $engagement->validationsWorkflow()->delete();

            $engagement->update([
                'statut' => StatutEngagement::Soumis,
                'submitted_at' => now(),
            ]);

            $engagement->refresh();

            $this->bootstrapWorkflow($engagement);

            $engagementId = $engagement->id;
            $numero = $engagement->numero;

            DB::afterCommit(function () use ($user, $engagementId, $numero): void {
                BudgetJournal::record($user, __('journal.workflow.submitted', ['numero' => $numero]), Engagement::find($engagementId), [
                    'engagement_id' => $engagementId,
                ]);
            });

            DB::afterCommit(function () use ($engagementId, $fromStatus, $user): void {
                event(new EngagementStatusChanged(
                    $engagementId,
                    $fromStatus->value,
                    StatutEngagement::Soumis->value,
                    $user->id
                ));
            });
        });
    }

    public function bootstrapWorkflow(Engagement $engagement): void
    {
        $definitions = config('engagement_workflow.steps', []);
        $ordre = 1;

        foreach ($definitions as $step) {
            ValidationWorkflow::query()->create([
                'engagement_id' => $engagement->id,
                'etape' => $step['key'],
                'ordre' => $ordre++,
                'decision' => DecisionWorkflow::EnAttente,
            ]);
        }
    }

    public function currentPendingStep(Engagement $engagement): ?ValidationWorkflow
    {
        return $engagement->validationsWorkflow()
            ->where('decision', DecisionWorkflow::EnAttente)
            ->orderBy('ordre')
            ->first();
    }

    public function userCanValidateStep(User $user, ValidationWorkflow $step): bool
    {
        $engagement = $step->engagement()->first();
        if ($engagement !== null && $engagement->created_by === $user->id && ! $user->can('exercises.manage')) {
            return false;
        }

        if ($user->can('exercises.manage')) {
            return true;
        }

        return $this->userMatchesConfiguredPermission($user, $step);
    }

    public function processDecision(
        Engagement $engagement,
        User $user,
        DecisionWorkflow $decision,
        ?string $commentaire,
    ): void {
        if (! in_array($decision, [DecisionWorkflow::Valide, DecisionWorkflow::Rejete, DecisionWorkflow::Retourne], true)) {
            throw ValidationException::withMessages([
                'decision' => __('Décision invalide.'),
            ]);
        }

        DB::transaction(function () use ($engagement, $user, $decision, $commentaire): void {
            Engagement::query()->whereKey($engagement->id)->lockForUpdate()->firstOrFail();

            $engagement->refresh();

            if (! $engagement->statut->estEnCircuitValidation()) {
                throw ValidationException::withMessages([
                    'workflow' => __('Cet engagement n’est pas en circuit de validation.'),
                ]);
            }

            if ($engagement->statut === StatutEngagement::Soumis) {
                $engagement->update(['statut' => StatutEngagement::EnValidation]);
                $engagement->refresh();
            }

            $step = ValidationWorkflow::query()
                ->where('engagement_id', $engagement->id)
                ->where('decision', DecisionWorkflow::EnAttente)
                ->orderBy('ordre')
                ->lockForUpdate()
                ->first();

            if ($step === null) {
                throw ValidationException::withMessages([
                    'workflow' => __('Aucune étape en attente.'),
                ]);
            }

            if (! $this->userCanValidateStep($user, $step)) {
                abort(403);
            }

            $stepEtape = $step->etape;
            $engagementId = $engagement->id;

            $step->update([
                'validateur_id' => $user->id,
                'decision' => $decision,
                'commentaire' => $commentaire,
                'date_decision' => now(),
            ]);

            match ($decision) {
                DecisionWorkflow::Valide => $this->afterValidateStep($engagement, $user),
                DecisionWorkflow::Rejete => $this->afterReject($engagement, $commentaire),
                DecisionWorkflow::Retourne => $this->afterReturn($engagement),
                default => null,
            };

            $decisionValue = $decision->value;
            $commentaireLog = $commentaire;

            DB::afterCommit(function () use ($user, $engagementId, $stepEtape, $decisionValue, $commentaireLog): void {
                $model = Engagement::find($engagementId);
                if ($model === null) {
                    return;
                }
                $numero = $model->numero;
                $libEtape = __('workflow.steps.'.$stepEtape);

                match ($decisionValue) {
                    'valide' => BudgetJournal::record($user, __('journal.workflow.step_validated', [
                        'etape' => $libEtape,
                        'numero' => $numero,
                    ]), $model, ['engagement_id' => $engagementId, 'etape' => $stepEtape]),
                    'rejete' => BudgetJournal::record($user, __('journal.workflow.rejected', [
                        'numero' => $numero,
                        'etape' => $libEtape,
                    ]), $model, ['engagement_id' => $engagementId, 'etape' => $stepEtape, 'commentaire' => $commentaireLog]),
                    'retourne' => BudgetJournal::record($user, __('journal.workflow.returned', [
                        'numero' => $numero,
                        'etape' => $libEtape,
                    ]), $model, ['engagement_id' => $engagementId, 'etape' => $stepEtape, 'commentaire' => $commentaireLog]),
                    default => null,
                };
            });
        });
    }

    protected function userMatchesConfiguredPermission(User $user, ValidationWorkflow $step): bool
    {
        $definition = collect(config('engagement_workflow.steps'))->firstWhere('key', $step->etape);

        if ($definition === null) {
            return false;
        }

        if (! empty($definition['permissions_any'])) {
            return $user->hasAnyPermission($definition['permissions_any']);
        }

        return $user->can($definition['permission']);
    }

    protected function afterValidateStep(Engagement $engagement, User $user): void
    {
        $stillPending = ValidationWorkflow::query()
            ->where('engagement_id', $engagement->id)
            ->where('decision', DecisionWorkflow::EnAttente)
            ->exists();

        if (! $stillPending) {
            $this->finalizeApproval($engagement, $user);
        }
    }

    protected function afterReject(Engagement $engagement, ?string $commentaire): void
    {
        $fromStatus = $engagement->statut;

        ValidationWorkflow::query()
            ->where('engagement_id', $engagement->id)
            ->where('decision', DecisionWorkflow::EnAttente)
            ->delete();

        $engagement->update([
            'statut' => StatutEngagement::Rejete,
            'rejected_at' => now(),
            'motif_rejet' => $commentaire,
        ]);

        DB::afterCommit(function () use ($engagement, $fromStatus): void {
            event(new EngagementStatusChanged(
                $engagement->id,
                $fromStatus->value,
                StatutEngagement::Rejete->value
            ));
        });
    }

    protected function afterReturn(Engagement $engagement): void
    {
        $fromStatus = $engagement->statut;

        ValidationWorkflow::query()
            ->where('engagement_id', $engagement->id)
            ->where('decision', DecisionWorkflow::EnAttente)
            ->delete();

        $engagement->update([
            'statut' => StatutEngagement::Retourne,
        ]);

        DB::afterCommit(function () use ($engagement, $fromStatus): void {
            event(new EngagementStatusChanged(
                $engagement->id,
                $fromStatus->value,
                StatutEngagement::Retourne->value
            ));
        });
    }

    protected function finalizeApproval(Engagement $engagement, User $user): void
    {
        $lastValidatedStep = ValidationWorkflow::query()
            ->where('engagement_id', $engagement->id)
            ->where('decision', DecisionWorkflow::Valide)
            ->orderByDesc('ordre')
            ->first();
        $isDelegatedApproval = $lastValidatedStep?->etape === 'visa_ordonnateur' && $user->can('workflow.ordonnateur_delegue');
        if ($isDelegatedApproval) {
            $this->assertDelegatedThresholdRespected($engagement);
        }

        $imputations = $engagement->imputations()->get();
        if ($imputations->isEmpty()) {
            $imputations = collect([(object) [
                'ligne_budgetaire_id' => $engagement->ligne_budgetaire_id,
                'montant_impute' => $engagement->montant,
            ]]);
        }

        foreach ($imputations as $imputation) {
            $ligne = LigneBudgetaire::query()
                ->whereKey($imputation->ligne_budgetaire_id)
                ->lockForUpdate()
                ->firstOrFail();

            $disponible = $ligne->creditDisponible();
            $montantImpute = (string) $imputation->montant_impute;

            if (bccomp($montantImpute, $disponible, 2) > 0) {
                throw ValidationException::withMessages([
                    'workflow' => __('Le crédit disponible sur la ligne #:ligne est insuffisant (disponible : :d).', [
                        'ligne' => $ligne->id,
                        'd' => $disponible,
                    ]),
                ]);
            }

            $ligne->update([
                'montant_engage' => bcadd((string) $ligne->montant_engage, $montantImpute, 2),
            ]);
        }

        $engagement->update([
            'statut' => StatutEngagement::Approuve,
            'approved_at' => now(),
        ]);

        $engagementId = $engagement->id;
        $numero = $engagement->numero;
        $montantStr = (string) $engagement->montant;
        $imputationsPayload = $imputations->map(fn ($row) => [
            'ligne_budgetaire_id' => (int) $row->ligne_budgetaire_id,
            'montant_impute' => (string) $row->montant_impute,
        ])->values()->all();

        DB::afterCommit(function () use ($user, $engagementId, $numero, $montantStr, $imputationsPayload): void {
            BudgetJournal::record($user, __('journal.workflow.approved', [
                'numero' => $numero,
                'montant' => $montantStr,
                'ligne' => $imputationsPayload[0]['ligne_budgetaire_id'] ?? '-',
            ]), Engagement::find($engagementId), [
                'engagement_id' => $engagementId,
                'montant' => $montantStr,
                'imputations' => $imputationsPayload,
            ]);

            event(new EngagementApproved($engagementId));
            event(new EngagementStatusChanged(
                $engagementId,
                StatutEngagement::EnValidation->value,
                StatutEngagement::Approuve->value,
                $user->id
            ));
        });
    }

    protected function assertDelegatedThresholdRespected(Engagement $engagement): void
    {
        $threshold = (float) (ParametreControle::query()
            ->where('cle', 'seuil_ordonnateur_delegue')
            ->where('actif', true)
            ->value('valeur') ?? 0);

        if ((float) $engagement->montant > $threshold) {
            throw ValidationException::withMessages([
                'workflow' => __('Validation déléguée impossible : le montant dépasse le seuil autorisé (:seuil).', [
                    'seuil' => number_format($threshold, 2, ',', ' '),
                ]),
            ]);
        }
    }

    /**
     * Clôture administrative d'un engagement intégralement payé.
     */
    public function cloturer(Engagement $engagement, User $user): void
    {
        if (! $engagement->statut->estCloturable()) {
            throw ValidationException::withMessages([
                'workflow' => __('Seul un engagement intégralement payé peut être clôturé.'),
            ]);
        }

        DB::transaction(function () use ($engagement, $user): void {
            $fromStatus = $engagement->statut;

            $engagement->update(['statut' => StatutEngagement::Cloture]);

            $engagementId = $engagement->id;
            $numero = $engagement->numero;

            DB::afterCommit(function () use ($user, $engagementId, $numero, $fromStatus): void {
                BudgetJournal::record($user, __('Clôture de l’engagement :numero.', ['numero' => $numero]), Engagement::find($engagementId), [
                    'engagement_id' => $engagementId,
                ]);

                event(new EngagementStatusChanged(
                    $engagementId,
                    $fromStatus->value,
                    StatutEngagement::Cloture->value,
                    $user->id
                ));
            });
        });
    }
}
