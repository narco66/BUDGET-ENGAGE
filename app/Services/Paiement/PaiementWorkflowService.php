<?php

namespace App\Services\Paiement;

use App\Enums\DecisionWorkflow;
use App\Enums\StatutPaiement;
use App\Models\Paiement;
use App\Models\PaiementValidation;
use App\Models\User;
use App\Support\BudgetJournal;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Circuit de validation paramétrable du paiement (config paiement_workflow).
 * Brouillon → Programmation → Contrôle trésorerie → Contrôle comptable →
 * Validation financière → Validation signataires → Validé. L'exécution bancaire,
 * le rapprochement et la clôture relèvent de PaiementService.
 */
class PaiementWorkflowService
{
    public function __construct(
        private readonly PaiementValidationService $validation,
        private readonly PaiementAuditService $audit,
    ) {
    }

    public function programmer(Paiement $paiement, User $user): void
    {
        $definitions = config('paiement_workflow.steps', []);

        if ($definitions === []) {
            throw ValidationException::withMessages(['workflow' => __('Aucune étape de validation n’est configurée.')]);
        }

        if (! ($paiement->statut === null || $paiement->statut->estModifiable())) {
            throw ValidationException::withMessages([
                'workflow' => __('Seul un paiement en brouillon ou retourné peut être programmé.'),
            ]);
        }

        $this->validation->assertPretPourSoumission($paiement);

        DB::transaction(function () use ($paiement, $user): void {
            Paiement::query()->whereKey($paiement->id)->lockForUpdate()->firstOrFail();

            $paiement->validations()->delete();
            $paiement->update([
                'statut' => StatutPaiement::Programme,
                'programmed_at' => now(),
            ]);
            $this->bootstrap($paiement);

            $this->audit->record($paiement, $user, 'programmation', 'Programmation du paiement et soumission au circuit.');

            $id = $paiement->id;
            $numero = (string) $paiement->numero;
            DB::afterCommit(function () use ($user, $id, $numero): void {
                BudgetJournal::record($user, "Programmation du paiement {$numero}.", Paiement::find($id), [
                    'paiement_id' => $id,
                ]);
            });
        });
    }

    public function bootstrap(Paiement $paiement): void
    {
        $ordre = 1;
        foreach (config('paiement_workflow.steps', []) as $step) {
            $paiement->validations()->create([
                'etape' => $step['key'],
                'ordre' => $ordre++,
                'decision' => DecisionWorkflow::EnAttente,
            ]);
        }
    }

    public function currentPendingStep(Paiement $paiement): ?PaiementValidation
    {
        return $paiement->validations()
            ->where('decision', DecisionWorkflow::EnAttente)
            ->orderBy('ordre')
            ->first();
    }

    public function userCanValidateStep(User $user, PaiementValidation $step): bool
    {
        $paiement = $step->paiement()->first();

        if ($paiement !== null && $paiement->created_by === $user->id && ! $user->can('paiement.admin')) {
            return false;
        }

        if ($user->can('paiement.admin')) {
            return true;
        }

        return $this->userMatchesConfiguredPermission($user, $step);
    }

    public function processDecision(Paiement $paiement, User $user, DecisionWorkflow $decision, ?string $commentaire): void
    {
        if (! in_array($decision, [DecisionWorkflow::Valide, DecisionWorkflow::Rejete, DecisionWorkflow::Retourne], true)) {
            throw ValidationException::withMessages(['decision' => __('Décision invalide.')]);
        }

        DB::transaction(function () use ($paiement, $user, $decision, $commentaire): void {
            Paiement::query()->whereKey($paiement->id)->lockForUpdate()->firstOrFail();
            $paiement->refresh();

            if (! ($paiement->statut === StatutPaiement::Programme || $paiement->statut?->estEnCircuit())) {
                throw ValidationException::withMessages(['workflow' => __('Ce paiement n’est pas en circuit de validation.')]);
            }

            $step = $paiement->validations()
                ->where('decision', DecisionWorkflow::EnAttente)
                ->orderBy('ordre')
                ->lockForUpdate()
                ->first();

            if ($step === null) {
                throw ValidationException::withMessages(['workflow' => __('Aucune étape en attente.')]);
            }

            if (! $this->userCanValidateStep($user, $step)) {
                abort(403);
            }

            $step->update([
                'validateur_id' => $user->id,
                'decision' => $decision,
                'commentaire' => $commentaire,
                'date_decision' => now(),
            ]);

            match ($decision) {
                DecisionWorkflow::Valide => $this->afterValidate($paiement, $step, $user),
                DecisionWorkflow::Rejete => $this->afterReject($paiement, $commentaire),
                DecisionWorkflow::Retourne => $this->afterReturn($paiement),
                default => null,
            };

            $etape = $step->etape;
            $decisionValue = $decision->value;
            $this->audit->record($paiement, $user, 'workflow_'.$decisionValue, "Décision « {$decisionValue} » à l’étape {$etape}.", [
                'etape' => $etape,
                'commentaire' => $commentaire,
            ]);

            $id = $paiement->id;
            $numero = (string) $paiement->numero;
            DB::afterCommit(function () use ($user, $id, $numero, $etape, $decisionValue): void {
                BudgetJournal::record($user, "Paiement {$numero} : {$decisionValue} à l’étape {$etape}.", Paiement::find($id), [
                    'paiement_id' => $id,
                    'etape' => $etape,
                    'decision' => $decisionValue,
                ]);
            });
        });
    }

    protected function afterValidate(Paiement $paiement, PaiementValidation $step, User $user): void
    {
        $definition = $this->stepDefinition($step->etape);
        if ($definition !== null && ! empty($definition['jalon'])) {
            $paiement->update([$definition['jalon'] => now()]);
        }

        $next = $this->currentPendingStep($paiement);

        if ($next === null) {
            $paiement->update([
                'statut' => StatutPaiement::Valide,
                'validation_signataires_at' => $paiement->validation_signataires_at ?? now(),
                'validated_by' => $user->id,
            ]);

            return;
        }

        $nextDefinition = $this->stepDefinition($next->etape);
        if ($nextDefinition !== null && ! empty($nextDefinition['statut'])) {
            $paiement->update(['statut' => StatutPaiement::from($nextDefinition['statut'])]);
        }
    }

    protected function afterReject(Paiement $paiement, ?string $commentaire): void
    {
        $paiement->validations()->where('decision', DecisionWorkflow::EnAttente)->delete();
        $paiement->update(['statut' => StatutPaiement::Rejete, 'motif_annulation' => $commentaire]);
    }

    protected function afterReturn(Paiement $paiement): void
    {
        $paiement->validations()->where('decision', DecisionWorkflow::EnAttente)->delete();
        $paiement->update(['statut' => StatutPaiement::Retourne]);
    }

    protected function userMatchesConfiguredPermission(User $user, PaiementValidation $step): bool
    {
        $definition = $this->stepDefinition($step->etape);

        if ($definition === null) {
            return false;
        }

        if (! empty($definition['permissions_any'])) {
            return $user->hasAnyPermission($definition['permissions_any']);
        }

        return $user->can($definition['permission']);
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function stepDefinition(string $key): ?array
    {
        foreach (config('paiement_workflow.steps', []) as $step) {
            if (($step['key'] ?? null) === $key) {
                return $step;
            }
        }

        return null;
    }
}
