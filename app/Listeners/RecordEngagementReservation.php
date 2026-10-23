<?php

namespace App\Listeners;

use App\Events\EngagementApproved;
use App\Models\Engagement;
use App\Services\Engagement\EngagementAuditService;
use App\Services\Engagement\EngagementReservationService;

/**
 * Alimente le registre des réservations de crédit à l'approbation d'un
 * engagement, sans interférer avec l'écriture budgétaire (déjà réalisée par
 * EngagementWorkflowService::finalizeApproval).
 */
class RecordEngagementReservation
{
    public function __construct(
        private readonly EngagementReservationService $reservations,
        private readonly EngagementAuditService $audit,
    ) {
    }

    public function handle(EngagementApproved $event): void
    {
        $engagement = Engagement::query()->find($event->engagementId);
        if ($engagement === null) {
            return;
        }

        $this->reservations->recordForEngagement($engagement);
        $this->audit->record($engagement, null, 'reservation_credit', 'Réservation des crédits enregistrée à l’approbation.');
    }
}
