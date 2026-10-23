<?php

namespace App\Services\Liquidation;

use App\Models\Liquidation;
use App\Models\LiquidationHistorique;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Journal d'audit dédié à la liquidation. Écrit dans la table
 * liquidation_historiques (timeline de la fiche) en complément du journal
 * budgétaire global (BudgetJournal) alimenté pour les opérations critiques.
 */
class LiquidationAuditService
{
    /**
     * @param  array<string, mixed>  $properties
     */
    public function record(
        Liquidation $liquidation,
        ?User $user,
        string $action,
        ?string $description = null,
        array $properties = [],
    ): LiquidationHistorique {
        return LiquidationHistorique::query()->create([
            'liquidation_id' => $liquidation->id,
            'action' => $action,
            'description' => $description ?? $action,
            'properties' => $properties ?: null,
            'user_id' => $user?->id,
        ]);
    }

    /**
     * @return Collection<int, LiquidationHistorique>
     */
    public function timeline(Liquidation $liquidation): Collection
    {
        return LiquidationHistorique::query()
            ->where('liquidation_id', $liquidation->id)
            ->with('user')
            ->latest()
            ->get();
    }
}
