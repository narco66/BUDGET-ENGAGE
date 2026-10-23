<?php

namespace Tests\Unit\ChaineDepense;

use App\Enums\StatutBesoin;
use App\Support\ChaineDepense\StatutPresenter;
use Tests\TestCase;

class ChaineDepensePresenterTest extends TestCase
{
    public function test_engagement_statut_presenter_returns_label_and_class(): void
    {
        $presented = StatutPresenter::engagement('approuve');

        $this->assertSame('Approuvé', $presented['label']);
        $this->assertSame('badge-approuve', $presented['class']);
    }

    public function test_besoin_statut_presenter_uses_enum_libelle(): void
    {
        $presented = StatutPresenter::besoin(StatutBesoin::Converti);

        $this->assertSame('Converti en engagement', $presented['label']);
    }

    public function test_liquidation_statut_certifie_is_success_tone(): void
    {
        $presented = StatutPresenter::liquidation('certifie');

        $this->assertSame('Certifiée', $presented['label']);
        $this->assertSame('badge-success', $presented['class']);
    }

    public function test_paiement_statut_execute_is_success(): void
    {
        $presented = StatutPresenter::paiement('execute');

        $this->assertSame('Exécuté', $presented['label']);
        $this->assertSame('badge-success', $presented['class']);
    }
}
