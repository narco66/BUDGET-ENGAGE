<?php

namespace Tests\Unit;

use App\Models\Engagement;
use App\Services\RiskScoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RiskScoringServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_compute_returns_expected_score_with_high_risk_inputs(): void
    {
        $engagement = Engagement::factory()->create([
            'montant' => 150_000_000,
            'is_urgence' => true,
            'type_engagement' => 'salaire',
            'beneficiaire_type' => 'autre',
        ]);

        $score = app(RiskScoringService::class)->compute($engagement->fresh('piecesJustificatives'));

        $this->assertSame(100, $score);
    }

    public function test_compute_returns_lower_score_with_normal_inputs(): void
    {
        $engagement = Engagement::factory()->create([
            'montant' => 500_000,
            'is_urgence' => false,
            'type_engagement' => 'achat',
            'beneficiaire_type' => 'autre',
        ]);

        $score = app(RiskScoringService::class)->compute($engagement->fresh('piecesJustificatives'));

        $this->assertSame(45, $score);
    }
}
