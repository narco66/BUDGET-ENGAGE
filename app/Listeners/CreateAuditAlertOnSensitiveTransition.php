<?php

namespace App\Listeners;

use App\Enums\NiveauRisqueAlerte;
use App\Enums\StatutTraitementAlerte;
use App\Events\EngagementStatusChanged;
use App\Models\AlerteAudit;
use App\Models\Engagement;

class CreateAuditAlertOnSensitiveTransition
{
    public function handle(EngagementStatusChanged $event): void
    {
        $engagement = Engagement::query()->find($event->engagementId);

        if ($engagement === null) {
            return;
        }

        $transitionKey = "{$event->from}->{$event->to}";
        $sensitive = $this->sensitiveTransitions()[$transitionKey] ?? null;

        if ($sensitive === null) {
            return;
        }

        AlerteAudit::query()->create([
            'engagement_id' => $engagement->id,
            'code_alerte' => 'TRANSITION_STATUT_SENSIBLE',
            'type_alerte' => 'transition_statut',
            'niveau_risque' => $sensitive['niveau'],
            'score_risque' => $sensitive['score'],
            'description' => "Transition sensible détectée: {$event->from} -> {$event->to} ({$engagement->numero})",
            'statut_traitement' => StatutTraitementAlerte::Nouvelle,
            'detectee_le' => now(),
            'commentaire_traitement' => $event->triggeredBy !== null
                ? "Déclenchée par l'utilisateur #{$event->triggeredBy}"
                : null,
        ]);

        if ($event->to === 'approuve') {
            $this->createDocumentaryAlerts($engagement);
        }
    }

    /**
     * @return array<string, array{niveau: NiveauRisqueAlerte, score: int}>
     */
    protected function sensitiveTransitions(): array
    {
        return [
            'en_validation->rejete' => ['niveau' => NiveauRisqueAlerte::Eleve, 'score' => 75],
            'en_validation->retourne' => ['niveau' => NiveauRisqueAlerte::Moyen, 'score' => 60],
            'en_validation->approuve' => ['niveau' => NiveauRisqueAlerte::Moyen, 'score' => 55],
            'approuve->annule' => ['niveau' => NiveauRisqueAlerte::Critique, 'score' => 90],
            'approuve->liquide' => ['niveau' => NiveauRisqueAlerte::Moyen, 'score' => 50],
            'liquide->ordonnance' => ['niveau' => NiveauRisqueAlerte::Moyen, 'score' => 50],
            'ordonnance->paye' => ['niveau' => NiveauRisqueAlerte::Faible, 'score' => 35],
        ];
    }

    protected function createDocumentaryAlerts(Engagement $engagement): void
    {
        if ($engagement->is_urgence && blank($engagement->justification_urgence)) {
            AlerteAudit::query()->create([
                'engagement_id' => $engagement->id,
                'code_alerte' => 'AUD-URG-001',
                'type_alerte' => 'urgence_sans_justification',
                'niveau_risque' => NiveauRisqueAlerte::Critique,
                'score_risque' => 90,
                'description' => "Urgence sans justification sur engagement {$engagement->numero}",
                'statut_traitement' => StatutTraitementAlerte::Nouvelle,
                'detectee_le' => now(),
            ]);
        }

        if ($engagement->piecesJustificatives()->count() === 0) {
            AlerteAudit::query()->create([
                'engagement_id' => $engagement->id,
                'code_alerte' => 'AUD-DOC-001',
                'type_alerte' => 'pieces_incompletes',
                'niveau_risque' => NiveauRisqueAlerte::Eleve,
                'score_risque' => 80,
                'description' => "Dossier approuve sans piece justificative: {$engagement->numero}",
                'statut_traitement' => StatutTraitementAlerte::Nouvelle,
                'detectee_le' => now(),
            ]);
        }
    }
}
