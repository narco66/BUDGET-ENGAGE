<?php

namespace App\Services;

use App\Enums\DecisionWorkflow;
use App\Enums\StatutEngagement;
use App\Models\Engagement;
use App\Models\LigneBudgetaire;
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

            $engagement->validationsWorkflow()->delete();

            $engagement->update([
                'statut' => StatutEngagement::EnValidation,
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

            if ($engagement->statut !== StatutEngagement::EnValidation) {
                throw ValidationException::withMessages([
                    'workflow' => __('Cet engagement n’est pas en circuit de validation.'),
                ]);
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
        ValidationWorkflow::query()
            ->where('engagement_id', $engagement->id)
            ->where('decision', DecisionWorkflow::EnAttente)
            ->delete();

        $engagement->update([
            'statut' => StatutEngagement::Rejete,
            'rejected_at' => now(),
            'motif_rejet' => $commentaire,
        ]);
    }

    protected function afterReturn(Engagement $engagement): void
    {
        ValidationWorkflow::query()
            ->where('engagement_id', $engagement->id)
            ->where('decision', DecisionWorkflow::EnAttente)
            ->delete();

        $engagement->update([
            'statut' => StatutEngagement::Retourne,
        ]);
    }

    protected function finalizeApproval(Engagement $engagement, User $user): void
    {
        $ligne = LigneBudgetaire::query()
            ->whereKey($engagement->ligne_budgetaire_id)
            ->lockForUpdate()
            ->firstOrFail();

        $disponible = $ligne->creditDisponible();

        if (bccomp((string) $engagement->montant, $disponible, 2) > 0) {
            throw ValidationException::withMessages([
                'workflow' => __('Le crédit disponible sur la ligne est insuffisant pour approuver cet engagement (disponible : :d).', [
                    'd' => $disponible,
                ]),
            ]);
        }

        $nouveauMontantEngage = bcadd((string) $ligne->montant_engage, (string) $engagement->montant, 2);

        $ligne->update([
            'montant_engage' => $nouveauMontantEngage,
        ]);

        $engagement->update([
            'statut' => StatutEngagement::Approuve,
            'approved_at' => now(),
        ]);

        $engagementId = $engagement->id;
        $numero = $engagement->numero;
        $montantStr = (string) $engagement->montant;
        $ligneId = $ligne->id;

        DB::afterCommit(function () use ($user, $engagementId, $numero, $montantStr, $ligneId): void {
            BudgetJournal::record($user, __('journal.workflow.approved', [
                'numero' => $numero,
                'montant' => $montantStr,
                'ligne' => $ligneId,
            ]), Engagement::find($engagementId), [
                'engagement_id' => $engagementId,
                'ligne_budgetaire_id' => $ligneId,
                'montant' => $montantStr,
            ]);
        });
    }
}
