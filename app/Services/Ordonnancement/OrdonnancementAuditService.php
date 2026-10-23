<?php

namespace App\Services\Ordonnancement;

use App\Models\Ordonnancement;
use App\Models\OrdonnancementHistorique;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Journal d'audit dédié à l'ordonnancement (table ordonnancement_historiques),
 * en complément du journal budgétaire global (BudgetJournal).
 */
class OrdonnancementAuditService
{
    /**
     * @param  array<string, mixed>  $properties
     */
    public function record(
        Ordonnancement $ordonnancement,
        ?User $user,
        string $action,
        ?string $description = null,
        array $properties = [],
    ): OrdonnancementHistorique {
        return OrdonnancementHistorique::query()->create([
            'ordonnancement_id' => $ordonnancement->id,
            'action' => $action,
            'description' => $description ?? $action,
            'properties' => $properties ?: null,
            'user_id' => $user?->id,
        ]);
    }

    /**
     * @return Collection<int, OrdonnancementHistorique>
     */
    public function timeline(Ordonnancement $ordonnancement): Collection
    {
        return OrdonnancementHistorique::query()
            ->where('ordonnancement_id', $ordonnancement->id)
            ->with('user')
            ->latest()
            ->get();
    }
}
