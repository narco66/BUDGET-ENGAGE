<?php

namespace App\Observers;

use App\Models\LigneBudgetaire;
use App\Services\Budget\BudgetAlertService;

class LigneBudgetaireObserver
{
    public function __construct(private BudgetAlertService $alertes) {}

    public function saved(LigneBudgetaire $ligne): void
    {
        // Re-evalue le seuil d'alerte uniquement si la consommation ou le plafond a change.
        if ($ligne->wasChanged(['montant_engage', 'montant_revise', 'montant_vote'])
            || $ligne->wasRecentlyCreated) {
            $this->alertes->evaluate($ligne);
        }
    }
}
