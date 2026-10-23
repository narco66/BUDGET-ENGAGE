<?php

namespace App\Services\Paiement;

use App\Models\Paiement;
use App\Models\PaiementHistorique;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Journal d'audit dédié au paiement (table paiement_historiques), en complément
 * du journal budgétaire global (BudgetJournal).
 */
class PaiementAuditService
{
    /**
     * @param  array<string, mixed>  $properties
     */
    public function record(Paiement $paiement, ?User $user, string $action, ?string $description = null, array $properties = []): PaiementHistorique
    {
        return PaiementHistorique::query()->create([
            'paiement_id' => $paiement->id,
            'action' => $action,
            'description' => $description ?? $action,
            'properties' => $properties ?: null,
            'user_id' => $user?->id,
        ]);
    }

    /**
     * @return Collection<int, PaiementHistorique>
     */
    public function timeline(Paiement $paiement): Collection
    {
        return PaiementHistorique::query()
            ->where('paiement_id', $paiement->id)
            ->with('user')
            ->latest()
            ->get();
    }
}
