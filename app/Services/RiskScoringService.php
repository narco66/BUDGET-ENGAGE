<?php

namespace App\Services;

use App\Models\Engagement;

class RiskScoringService
{
    public function compute(Engagement $engagement): int
    {
        $score = 20;

        if ($engagement->is_urgence) {
            $score += 20;
        }

        $score += $this->scoreByMontant((float) $engagement->montant);

        if (in_array($engagement->type_engagement, ['salaire', 'charge'], true)) {
            $score += 15;
        }

        if ($engagement->beneficiaire_type?->value === 'autre') {
            $score += 10;
        }

        if ($engagement->piecesJustificatives->isEmpty()) {
            $score += 10;
        }

        return max(0, min(100, $score));
    }

    protected function scoreByMontant(float $montant): int
    {
        return match (true) {
            $montant >= 100_000_000 => 30,
            $montant >= 10_000_000 => 20,
            $montant >= 1_000_000 => 10,
            default => 5,
        };
    }
}
