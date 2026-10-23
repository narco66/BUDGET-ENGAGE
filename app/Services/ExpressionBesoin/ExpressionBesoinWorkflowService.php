<?php

namespace App\Services\ExpressionBesoin;

use App\Enums\StatutBesoin;
use App\Enums\StatutValidationGed;
use App\Models\ExpressionBesoin;
use App\Models\ExpressionBesoinValidation;
use App\Models\User;
use App\Notifications\BesoinDecisionNotification;
use App\Notifications\BesoinPendingValidationNotification;
use App\Services\Budget\CreditAvailabilityService;
use App\Support\BudgetJournal;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Circuit de validation hiérarchique d'une expression de besoin :
 * Soumis → Chef de Service → Directeur → Département → Contrôle Budgétaire →
 * Validation Finale. Les étapes proviennent de config/expression_besoin_workflow.php.
 */
class ExpressionBesoinWorkflowService
{
    public function __construct(
        private readonly ExpressionBesoinAuditService $audit,
        private readonly CreditAvailabilityService $creditAvailability,
    ) {
    }

    /**
     * Soumet le besoin au circuit de validation.
     */
    public function submit(ExpressionBesoin $besoin, User $user): void
    {
        if (! in_array($besoin->statut, [StatutBesoin::Brouillon, StatutBesoin::Retourne], true)) {
            throw ValidationException::withMessages(['workflow' => __('Ce besoin ne peut pas être soumis dans son état actuel.')]);
        }

        if (blank($besoin->justification)) {
            throw ValidationException::withMessages(['workflow' => __('La justification est obligatoire avant soumission.')]);
        }

        if ($besoin->lignes()->count() === 0) {
            throw ValidationException::withMessages(['workflow' => __('Le besoin doit comporter au moins une ligne avant soumission.')]);
        }

        $steps = config('expression_besoin_workflow.steps', []);
        if ($steps === []) {
            throw ValidationException::withMessages(['workflow' => __('Aucune étape de validation configurée.')]);
        }

        DB::transaction(function () use ($besoin, $user, $steps): void {
            $besoin->validations()->delete();

            foreach (array_values($steps) as $index => $step) {
                $besoin->validations()->create([
                    'ordre' => $index + 1,
                    'libelle_etape' => $step['libelle'],
                    'type_etape' => 'validation',
                    'permission_requise' => $step['permission'] ?? null,
                    'statut' => StatutValidationGed::EnAttente->value,
                ]);
            }

            $besoin->update([
                'statut' => StatutBesoin::Soumis->value,
                'submitted_at' => now(),
                'updated_by' => $user->id,
            ]);

            BudgetJournal::record($user, "Soumission du besoin {$besoin->reference} au circuit de validation.", $besoin, [
                'expression_besoin_id' => $besoin->id,
            ]);
            $this->audit->record($besoin, $user, 'soumission', 'Soumission au circuit de validation.');

            $this->notifyPendingValidators($besoin->fresh());
        });
    }

    public function currentPendingStep(ExpressionBesoin $besoin): ?ExpressionBesoinValidation
    {
        return $besoin->validations()
            ->where('statut', StatutValidationGed::EnAttente->value)
            ->orderBy('ordre')
            ->first();
    }

    public function userCanValidate(User $user, ExpressionBesoinValidation $step): bool
    {
        if ($user->can('expression_besoins.admin')) {
            return true;
        }

        if ($step->permission_requise) {
            return $user->can($step->permission_requise);
        }

        return $user->can('expression_besoins.validate');
    }

    /**
     * Traite une décision : valide | rejete | retourne.
     */
    public function decide(ExpressionBesoin $besoin, User $user, string $decision, ?string $commentaire): void
    {
        if (! $besoin->statut->estEnCircuitValidation()) {
            throw ValidationException::withMessages(['workflow' => __('Ce besoin n’est pas en circuit de validation.')]);
        }

        $step = $this->currentPendingStep($besoin);

        if ($step === null) {
            throw ValidationException::withMessages(['workflow' => __('Aucune étape de validation en attente.')]);
        }

        if (! $this->userCanValidate($user, $step)) {
            throw ValidationException::withMessages(['workflow' => __("Vous n'êtes pas habilité à traiter cette étape.")]);
        }

        if ($decision === 'valide' && $step->permission_requise === 'workflow.budget_control') {
            $this->creditAvailability->assertBesoinLignes($besoin);
        }

        DB::transaction(function () use ($besoin, $user, $decision, $commentaire, $step): void {
            if ($besoin->statut === StatutBesoin::Soumis) {
                $besoin->update(['statut' => StatutBesoin::EnValidation->value, 'updated_by' => $user->id]);
            }
            $step->update([
                'validateur_id' => $user->id,
                'commentaire' => $commentaire,
                'date_action' => now(),
                'statut' => match ($decision) {
                    'valide' => StatutValidationGed::Valide->value,
                    'rejete' => StatutValidationGed::Rejete->value,
                    'retourne' => StatutValidationGed::Retourne->value,
                    default => throw ValidationException::withMessages(['decision' => __('Décision invalide.')]),
                },
                'motif_rejet' => in_array($decision, ['rejete', 'retourne'], true) ? $commentaire : null,
            ]);

            $this->audit->record($besoin, $user, 'decision_'.$decision, "Décision « {$decision} » sur l'étape {$step->libelle_etape}.", [
                'etape' => $step->libelle_etape,
                'commentaire' => $commentaire,
            ]);

            if ($decision === 'rejete') {
                $besoin->update(['statut' => StatutBesoin::Rejete->value, 'rejected_at' => now(), 'motif_rejet' => $commentaire, 'updated_by' => $user->id]);
                $this->journalAndNotify($besoin, $user, 'rejete', $commentaire);

                return;
            }

            if ($decision === 'retourne') {
                $besoin->update(['statut' => StatutBesoin::Retourne->value, 'motif_rejet' => $commentaire, 'updated_by' => $user->id]);
                $this->journalAndNotify($besoin, $user, 'retourne', $commentaire);

                return;
            }

            // valide : étape suivante ou clôture du circuit.
            BudgetJournal::record($user, "Étape « {$step->libelle_etape} » validée pour le besoin {$besoin->reference}.", $besoin, []);

            $next = $this->currentPendingStep($besoin->fresh());
            if ($next === null) {
                $besoin->update(['statut' => StatutBesoin::Valide->value, 'validated_at' => now(), 'updated_by' => $user->id]);
                $this->journalAndNotify($besoin, $user, 'valide', null);
            } else {
                $this->notifyPendingValidators($besoin->fresh());
            }
        });
    }

    /**
     * Annule un besoin non converti (brouillon, retourné, soumis ou en validation).
     */
    public function cancel(ExpressionBesoin $besoin, User $user, ?string $motif = null): void
    {
        if (! $besoin->statut->estAnnulable()) {
            throw ValidationException::withMessages(['workflow' => __('Ce besoin ne peut pas être annulé dans son état actuel.')]);
        }

        DB::transaction(function () use ($besoin, $user, $motif): void {
            $besoin->validations()->delete();

            $besoin->update([
                'statut' => StatutBesoin::Annule->value,
                'motif_rejet' => $motif,
                'updated_by' => $user->id,
            ]);

            BudgetJournal::record($user, "Annulation du besoin {$besoin->reference}.", $besoin, [
                'expression_besoin_id' => $besoin->id,
                'motif' => $motif,
            ]);
            $this->audit->record($besoin, $user, 'annulation', 'Annulation du besoin.', ['motif' => $motif]);
        });
    }

    private function journalAndNotify(ExpressionBesoin $besoin, User $user, string $decision, ?string $commentaire): void
    {
        DB::afterCommit(function () use ($besoin, $decision): void {
            $besoin->loadMissing('createur');
            $besoin->createur?->notify(new BesoinDecisionNotification($besoin, $decision));
        });
    }

    private function notifyPendingValidators(ExpressionBesoin $besoin): void
    {
        $step = $this->currentPendingStep($besoin);
        if ($step === null || ! $step->permission_requise) {
            return;
        }

        $permission = $step->permission_requise;
        $libelle = $step->libelle_etape;
        $besoinId = $besoin->id;

        DB::afterCommit(function () use ($permission, $libelle, $besoinId): void {
            $besoin = ExpressionBesoin::find($besoinId);
            if ($besoin === null) {
                return;
            }
            User::query()->permission($permission)->get()
                ->each(fn (User $u) => $u->notify(new BesoinPendingValidationNotification($besoin, $libelle)));
        });
    }
}
