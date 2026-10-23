<?php

namespace App\Services\Ordonnancement;

use App\Enums\DecisionWorkflow;
use App\Enums\StatutOrdonnancement;
use App\Models\Ordonnancement;
use App\Models\OrdonnancementValidation;
use App\Models\User;
use App\Support\BudgetJournal;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Circuit de validation paramétrable de l'ordonnancement (config
 * ordonnancement_workflow). Brouillon → Soumission → Contrôles (budgétaire /
 * comptable / financier) → Visa ordonnateur → Validé. La génération du mandat et
 * la transmission relèvent de OrdonnancementService / OrdonnancementToPaiementService.
 */
class OrdonnancementWorkflowService
{
    public function __construct(
        private readonly OrdonnancementValidationService $validation,
        private readonly OrdonnancementAuditService $audit,
    ) {
    }

    public function soumettre(Ordonnancement $ordonnancement, User $user): void
    {
        $definitions = config('ordonnancement_workflow.steps', []);

        if ($definitions === []) {
            throw ValidationException::withMessages(['workflow' => __('Aucune étape de validation n’est configurée.')]);
        }

        if (! ($ordonnancement->statut === null || $ordonnancement->statut->estModifiable())) {
            throw ValidationException::withMessages([
                'workflow' => __('Seul un ordonnancement en brouillon ou retourné peut être soumis.'),
            ]);
        }

        $this->validation->assertPretPourSoumission($ordonnancement);

        DB::transaction(function () use ($ordonnancement, $user): void {
            Ordonnancement::query()->whereKey($ordonnancement->id)->lockForUpdate()->firstOrFail();

            $ordonnancement->validations()->delete();
            $ordonnancement->update([
                'statut' => StatutOrdonnancement::Soumis,
                'submitted_at' => now(),
            ]);
            $this->bootstrap($ordonnancement);

            $this->audit->record($ordonnancement, $user, 'soumission', 'Soumission de l’ordonnancement au circuit de validation.');

            $id = $ordonnancement->id;
            $numero = (string) $ordonnancement->numero;
            DB::afterCommit(function () use ($user, $id, $numero): void {
                BudgetJournal::record($user, "Soumission de l’ordonnancement {$numero} au circuit.", Ordonnancement::find($id), [
                    'ordonnancement_id' => $id,
                ]);
            });
        });
    }

    public function bootstrap(Ordonnancement $ordonnancement): void
    {
        $ordre = 1;
        foreach (config('ordonnancement_workflow.steps', []) as $step) {
            $ordonnancement->validations()->create([
                'etape' => $step['key'],
                'ordre' => $ordre++,
                'decision' => DecisionWorkflow::EnAttente,
            ]);
        }
    }

    public function currentPendingStep(Ordonnancement $ordonnancement): ?OrdonnancementValidation
    {
        return $ordonnancement->validations()
            ->where('decision', DecisionWorkflow::EnAttente)
            ->orderBy('ordre')
            ->first();
    }

    public function userCanValidateStep(User $user, OrdonnancementValidation $step): bool
    {
        $ordonnancement = $step->ordonnancement()->first();

        if ($ordonnancement !== null && $ordonnancement->created_by === $user->id && ! $user->can('ordonnancement.admin')) {
            return false;
        }

        if ($user->can('ordonnancement.admin')) {
            return true;
        }

        return $this->userMatchesConfiguredPermission($user, $step);
    }

    public function processDecision(Ordonnancement $ordonnancement, User $user, DecisionWorkflow $decision, ?string $commentaire): void
    {
        if (! in_array($decision, [DecisionWorkflow::Valide, DecisionWorkflow::Rejete, DecisionWorkflow::Retourne], true)) {
            throw ValidationException::withMessages(['decision' => __('Décision invalide.')]);
        }

        DB::transaction(function () use ($ordonnancement, $user, $decision, $commentaire): void {
            Ordonnancement::query()->whereKey($ordonnancement->id)->lockForUpdate()->firstOrFail();
            $ordonnancement->refresh();

            if (! ($ordonnancement->statut === StatutOrdonnancement::Soumis || $ordonnancement->statut?->estEnCircuit())) {
                throw ValidationException::withMessages(['workflow' => __('Cet ordonnancement n’est pas en circuit de validation.')]);
            }

            $step = $ordonnancement->validations()
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
                DecisionWorkflow::Valide => $this->afterValidate($ordonnancement, $step, $user),
                DecisionWorkflow::Rejete => $this->afterReject($ordonnancement, $commentaire),
                DecisionWorkflow::Retourne => $this->afterReturn($ordonnancement),
                default => null,
            };

            $etape = $step->etape;
            $decisionValue = $decision->value;
            $this->audit->record($ordonnancement, $user, 'workflow_'.$decisionValue, "Décision « {$decisionValue} » à l’étape {$etape}.", [
                'etape' => $etape,
                'commentaire' => $commentaire,
            ]);

            $id = $ordonnancement->id;
            $numero = (string) $ordonnancement->numero;
            DB::afterCommit(function () use ($user, $id, $numero, $etape, $decisionValue): void {
                BudgetJournal::record($user, "Ordonnancement {$numero} : {$decisionValue} à l’étape {$etape}.", Ordonnancement::find($id), [
                    'ordonnancement_id' => $id,
                    'etape' => $etape,
                    'decision' => $decisionValue,
                ]);
            });
        });
    }

    protected function afterValidate(Ordonnancement $ordonnancement, OrdonnancementValidation $step, User $user): void
    {
        $definition = $this->stepDefinition($step->etape);
        if ($definition !== null && ! empty($definition['jalon'])) {
            $ordonnancement->update([$definition['jalon'] => now()]);
        }

        $next = $this->currentPendingStep($ordonnancement);

        if ($next === null) {
            $ordonnancement->update([
                'statut' => StatutOrdonnancement::Valide,
                'valide_at' => now(),
                'validated_by' => $user->id,
            ]);

            return;
        }

        $nextDefinition = $this->stepDefinition($next->etape);
        if ($nextDefinition !== null && ! empty($nextDefinition['statut'])) {
            $ordonnancement->update(['statut' => StatutOrdonnancement::from($nextDefinition['statut'])]);
        }
    }

    protected function afterReject(Ordonnancement $ordonnancement, ?string $commentaire): void
    {
        $ordonnancement->validations()->where('decision', DecisionWorkflow::EnAttente)->delete();
        $ordonnancement->update(['statut' => StatutOrdonnancement::Rejete, 'motif_annulation' => $commentaire]);
    }

    protected function afterReturn(Ordonnancement $ordonnancement): void
    {
        $ordonnancement->validations()->where('decision', DecisionWorkflow::EnAttente)->delete();
        $ordonnancement->update(['statut' => StatutOrdonnancement::Retourne]);
    }

    protected function userMatchesConfiguredPermission(User $user, OrdonnancementValidation $step): bool
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
        foreach (config('ordonnancement_workflow.steps', []) as $step) {
            if (($step['key'] ?? null) === $key) {
                return $step;
            }
        }

        return null;
    }
}
