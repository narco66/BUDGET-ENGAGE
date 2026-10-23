<?php

namespace App\Services\ExpressionBesoin;

use App\Models\ExpressionBesoin;
use App\Models\User;
use Illuminate\Support\Collection;
use Spatie\Activitylog\Models\Activity;

/**
 * Journal d'audit dédié au module Expression des Besoins.
 *
 * Trace toutes les opérations sensibles (création, modification, soumission,
 * validation, rejet, conversion…) dans le log d'activité `expression_besoin`,
 * et restitue la chronologie pour l'onglet « Historique » de la fiche.
 */
class ExpressionBesoinAuditService
{
    public const LOG_NAME = 'expression_besoin';

    /**
     * @param  array<string, mixed>  $properties
     */
    public function record(ExpressionBesoin $besoin, User $user, string $action, ?string $description = null, array $properties = []): void
    {
        activity(self::LOG_NAME)
            ->performedOn($besoin)
            ->causedBy($user)
            ->withProperties(array_merge(['action' => $action, 'reference' => $besoin->reference], $properties))
            ->log($description ?? $action);
    }

    /**
     * Chronologie complète des opérations sur un besoin.
     *
     * @return Collection<int, Activity>
     */
    public function timeline(ExpressionBesoin $besoin): Collection
    {
        return Activity::query()
            ->where('log_name', self::LOG_NAME)
            ->where('subject_type', $besoin->getMorphClass())
            ->where('subject_id', $besoin->id)
            ->with('causer')
            ->latest()
            ->get();
    }
}
