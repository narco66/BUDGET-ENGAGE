<?php

namespace App\Services\Ged;

use App\Enums\StatutDocumentGed;
use App\Enums\StatutValidationGed;
use App\Models\GedDocument;
use App\Models\GedValidation;
use App\Models\User;
use App\Notifications\GedDocumentDecisionNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Circuit de validation documentaire : instanciation des étapes à partir d'un
 * circuit configuré (ged_workflows / ged_workflow_etapes) ou d'une étape
 * générique, puis traitement des décisions (validation / rejet / retour).
 */
class GedWorkflowService
{
    public function __construct(private readonly GedDocumentService $documents)
    {
    }

    /**
     * Démarre le circuit de validation d'un document.
     */
    public function start(GedDocument $document, User $user): void
    {
        if ($document->statut === StatutDocumentGed::EnValidation) {
            throw ValidationException::withMessages([
                'workflow' => __('Ce document est déjà en cours de validation.'),
            ]);
        }

        if ($document->est_archive) {
            throw ValidationException::withMessages([
                'workflow' => __('Un document archivé ne peut pas être soumis au circuit.'),
            ]);
        }

        DB::transaction(function () use ($document, $user): void {
            $document->validations()->delete();

            foreach ($this->resolveSteps($document) as $ordre => $step) {
                $document->validations()->create([
                    'ged_workflow_etape_id' => $step['etape_id'],
                    'ordre' => $ordre + 1,
                    'libelle_etape' => $step['libelle'],
                    'type_etape' => $step['type_etape'],
                    'statut' => StatutValidationGed::EnAttente->value,
                ]);
            }

            $document->update([
                'statut' => StatutDocumentGed::EnValidation->value,
                'updated_by' => $user->id,
            ]);

            $this->documents->journal($document, $user, 'soumission_validation');
        });
    }

    public function currentPendingStep(GedDocument $document): ?GedValidation
    {
        return $document->validations()
            ->where('statut', StatutValidationGed::EnAttente->value)
            ->orderBy('ordre')
            ->first();
    }

    public function userCanValidate(User $user, GedValidation $step): bool
    {
        if ($user->can('ged.admin')) {
            return true;
        }

        $etape = $step->etape;

        if ($etape?->permission_requise) {
            return $user->can($etape->permission_requise);
        }

        if ($etape?->role_requis) {
            return $user->hasRole($etape->role_requis);
        }

        return $user->can('ged.validate');
    }

    /**
     * Traite une décision sur l'étape courante : valide | rejete | retourne.
     */
    public function decide(GedDocument $document, User $user, string $decision, ?string $commentaire): void
    {
        $step = $this->currentPendingStep($document);

        if ($step === null) {
            throw ValidationException::withMessages([
                'workflow' => __('Aucune étape de validation en attente.'),
            ]);
        }

        if (! $this->userCanValidate($user, $step)) {
            throw ValidationException::withMessages([
                'workflow' => __("Vous n'êtes pas habilité à traiter cette étape."),
            ]);
        }

        DB::transaction(function () use ($document, $user, $decision, $commentaire, $step): void {
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

            DB::afterCommit(function () use ($document, $decision): void {
                $document->loadMissing('createur');
                $document->createur?->notify(new GedDocumentDecisionNotification($document, $decision));
            });

            if ($decision === 'rejete') {
                $document->update(['statut' => StatutDocumentGed::Rejete->value, 'updated_by' => $user->id]);
                $this->documents->journal($document, $user, 'validation_rejetee', null, null, $commentaire);

                return;
            }

            if ($decision === 'retourne') {
                $document->update(['statut' => StatutDocumentGed::Depose->value, 'updated_by' => $user->id]);
                $this->documents->journal($document, $user, 'validation_retournee', null, null, $commentaire);

                return;
            }

            // Décision = valide : passer à l'étape suivante ou clôturer le circuit.
            $this->documents->journal($document, $user, 'etape_validee', null, ['etape' => $step->libelle_etape]);

            if ($this->currentPendingStep($document->fresh()) === null) {
                $document->update(['statut' => StatutDocumentGed::Valide->value, 'updated_by' => $user->id]);
                $this->documents->journal($document, $user, 'document_valide');
            }
        });
    }

    /**
     * Détermine les étapes du circuit : celles du circuit attaché, sinon une
     * étape de validation générique.
     *
     * @return array<int, array{etape_id:int|null, libelle:string, type_etape:string}>
     */
    private function resolveSteps(GedDocument $document): array
    {
        $document->loadMissing('workflow.etapes');
        $etapes = $document->workflow?->etapes ?? collect();

        if ($etapes->isNotEmpty()) {
            return $etapes->values()->map(fn ($etape) => [
                'etape_id' => $etape->id,
                'libelle' => $etape->libelle,
                'type_etape' => $etape->type_etape,
            ])->all();
        }

        return [[
            'etape_id' => null,
            'libelle' => __('Validation'),
            'type_etape' => 'validation',
        ]];
    }
}
