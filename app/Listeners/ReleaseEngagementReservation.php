<?php

namespace App\Listeners;

use App\Enums\StatutEngagement;
use App\Events\EngagementStatusChanged;
use App\Models\Engagement;
use App\Services\Engagement\EngagementAuditService;
use App\Services\Engagement\EngagementReservationService;

/**
 * Libère les réservations de crédit lorsqu'un engagement est annulé.
 */
class ReleaseEngagementReservation
{
    public function __construct(
        private readonly EngagementReservationService $reservations,
        private readonly EngagementAuditService $audit,
    ) {
    }

    public function handle(EngagementStatusChanged $event): void
    {
        if ($event->to !== StatutEngagement::Annule->value) {
            return;
        }

        $engagement = Engagement::query()->find($event->engagementId);
        if ($engagement === null) {
            return;
        }

        $this->reservations->releaseForEngagement($engagement);
        $this->audit->record($engagement, null, 'liberation_credit', 'Libération des crédits réservés (annulation).');
    }
}
