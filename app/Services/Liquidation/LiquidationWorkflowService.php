<?php

namespace App\Services\Liquidation;

use App\Enums\DecisionWorkflow;
use App\Enums\StatutLiquidation;
use App\Models\Liquidation;
use App\Models\LiquidationValidation;
use App\Models\User;
use App\Support\BudgetJournal;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Circuit de validation paramétrable de la liquidation (config
 * liquidation_workflow). Brouillon → Soumission → Contrôles
 * (technique / administratif / budgétaire / financier) → Validation.
 * La certification et la transmission relèvent de LiquidationService /
 * LiquidationToOrdonnancementService.
 */
class LiquidationWorkflowService
{
    public function __construct(
        private readonly LiquidationValidationService $validation,
        private readonly LiquidationAuditService $audit,
    ) {
    }

    public function soumettre(Liquidation $liquidation, User $user): void
    {
        $definitions = config('liquidation_workflow.steps', []);

        if ($definitions === []) {
            throw ValidationException::withMessages([
                'workflow' => __('Aucune étape de validation n’est configurée.'),
            ]);
        }

        if (! ($liquidation->statut === null || $liquidation->statut->estModifiable())) {
            throw ValidationException::withMessages([
                'workflow' => __('Seule une liquidation en brouillon ou retournée peut être soumise.'),
            ]);
        }

        $this->validation->assertPretPourSoumission($liquidation);

        DB::transaction(function () use ($liquidation, $user): void {
            Liquidation::query()->whereKey($liquidation->id)->lockForUpdate()->firstOrFail();

            $liquidation->validations()->delete();

            $liquidation->update([
                'statut' => StatutLiquidation::Soumis,
                'submitted_at' => now(),
            ]);

            $this->bootstrap($liquidation);

            $this->audit->record($liquidation, $user, 'soumission', 'Soumission de la liquidation au circuit de validation.');

            $liquidationId = $liquidation->id;
            $numero = (string) $liquidation->numero;
            DB::afterCommit(function () use ($user, $liquidationId, $numero): void {
                BudgetJournal::record($user, "Soumission de la liquidation {$numero} au circuit.", Liquidation::find($liquidationId), [
                    'liquidation_id' => $liquidationId,
                ]);
            });
        });
    }

    public function bootstrap(Liquidation $liquidation): void
    {
        $ordre = 1;
        foreach (config('liquidation_workflow.steps', []) as $step) {
            $liquidation->validations()->create([
                'etape' => $step['key'],
                'ordre' => $ordre++,
                'decision' => DecisionWorkflow::EnAttente,
            ]);
        }
    }

    public function currentPendingStep(Liquidation $liquidation): ?LiquidationValidation
    {
        return $liquidation->validations()
            ->where('decision', DecisionWorkflow::EnAttente)
            ->orderBy('ordre')
            ->first();
    }

    public function userCanValidateStep(User $user, LiquidationValidation $step): bool
    {
        $liquidation = $step->liquidation()->first();

        if ($liquidation !== null && $liquidation->created_by === $user->id && ! $user->can('liquidation.admin')) {
            return false;
        }

        if ($user->can('liquidation.admin')) {
            return true;
        }

        return $this->userMatchesConfiguredPermission($user, $step);
    }

    public function processDecision(Liquidation $liquidation, User $user, DecisionWorkflow $decision, ?string $commentaire): void
    {
        if (! in_array($decision, [DecisionWorkflow::Valide, DecisionWorkflow::Rejete, DecisionWorkflow::Retourne], true)) {
            throw ValidationException::withMessages(['decision' => __('Décision invalide.')]);
        }

        DB::transaction(function () use ($liquidation, $user, $decision, $commentaire): void {
            Liquidation::query()->whereKey($liquidation->id)->lockForUpdate()->firstOrFail();
            $liquidation->refresh();

            if (! ($liquidation->statut === StatutLiquidation::Soumis || $liquidation->statut?->estEnCircuit())) {
                throw ValidationException::withMessages([
                    'workflow' => __('Cette liquidation n’est pas en circuit de validation.'),
                ]);
            }

            $step = $liquidation->validations()
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
                DecisionWorkflow::Valide => $this->afterValidate($liquidation, $step, $user),
                DecisionWorkflow::Rejete => $this->afterReject($liquidation, $commentaire),
                DecisionWorkflow::Retourne => $this->afterReturn($liquidation),
                default => null,
            };

            $etape = $step->etape;
            $decisionValue = $decision->value;
            $this->audit->record($liquidation, $user, 'workflow_'.$decisionValue, "Décision « {$decisionValue} » à l’étape {$etape}.", [
                'etape' => $etape,
                'commentaire' => $commentaire,
            ]);

            $liquidationId = $liquidation->id;
            $numero = (string) $liquidation->numero;
            DB::afterCommit(function () use ($user, $liquidationId, $numero, $etape, $decisionValue): void {
                BudgetJournal::record($user, "Liquidation {$numero} : {$decisionValue} à l’étape {$etape}.", Liquidation::find($liquidationId), [
                    'liquidation_id' => $liquidationId,
                    'etape' => $etape,
                    'decision' => $decisionValue,
                ]);
            });
        });
    }

    protected function afterValidate(Liquidation $liquidation, LiquidationValidation $step, User $user): void
    {
        $definition = $this->stepDefinition($step->etape);
        if ($definition !== null && ! empty($definition['jalon'])) {
            $liquidation->update([$definition['jalon'] => now()]);
        }

        $next = $this->currentPendingStep($liquidation);

        if ($next === null) {
            $liquidation->update([
                'statut' => StatutLiquidation::Valide,
                'valide_at' => now(),
                'validated_by' => $user->id,
            ]);

            return;
        }

        $nextDefinition = $this->stepDefinition($next->etape);
        if ($nextDefinition !== null && ! empty($nextDefinition['statut'])) {
            $liquidation->update(['statut' => StatutLiquidation::from($nextDefinition['statut'])]);
        }
    }

    protected function afterReject(Liquidation $liquidation, ?string $commentaire): void
    {
        $liquidation->validations()->where('decision', DecisionWorkflow::EnAttente)->delete();
        $liquidation->update([
            'statut' => StatutLiquidation::Rejete,
            'motif_annulation' => $commentaire,
        ]);
    }

    protected function afterReturn(Liquidation $liquidation): void
    {
        $liquidation->validations()->where('decision', DecisionWorkflow::EnAttente)->delete();
        $liquidation->update(['statut' => StatutLiquidation::Retourne]);
    }

    protected function userMatchesConfiguredPermission(User $user, LiquidationValidation $step): bool
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
        foreach (config('liquidation_workflow.steps', []) as $step) {
            if (($step['key'] ?? null) === $key) {
                return $step;
            }
        }

        return null;
    }
}
