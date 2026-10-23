<?php

namespace Database\Seeders;

use App\Models\PaiementType;
use Illuminate\Database\Seeder;

class PaiementTypeSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->types() as $ordre => $type) {
            PaiementType::query()->updateOrCreate(
                ['code' => $type['code']],
                array_merge($type, ['ordre' => $ordre]),
            );
        }
    }

    /**
     * Référentiel administrable des types de paiement.
     *
     * @return array<int, array<string, mixed>>
     */
    private function types(): array
    {
        return [
            ['code' => 'PAY-VIREMENT', 'libelle' => 'Virement bancaire', 'famille' => 'bancaire', 'nature' => 'virement', 'documents_obligatoires' => ['ordre_virement', 'avis_debit']],
            ['code' => 'PAY-CHEQUE', 'libelle' => 'Chèque', 'famille' => 'bancaire', 'nature' => 'cheque', 'documents_obligatoires' => ['cheque_numerise']],
            ['code' => 'PAY-ESPECES', 'libelle' => 'Espèces', 'famille' => 'caisse', 'nature' => 'especes', 'exige_banque' => false, 'documents_obligatoires' => ['quittance']],
            ['code' => 'PAY-SWIFT', 'libelle' => 'SWIFT', 'famille' => 'international', 'nature' => 'swift', 'multi_devises' => true, 'documents_obligatoires' => ['avis_swift']],
            ['code' => 'PAY-SEPA', 'libelle' => 'SEPA', 'famille' => 'international', 'nature' => 'sepa', 'multi_devises' => true],
            ['code' => 'PAY-MOBILE', 'libelle' => 'Mobile money', 'famille' => 'electronique', 'nature' => 'mobile_money', 'documents_obligatoires' => ['recu']],
            ['code' => 'PAY-COMPENSATION', 'libelle' => 'Compensation', 'famille' => 'comptable', 'nature' => 'compensation', 'exige_banque' => false],
            ['code' => 'PAY-INTERNATIONAL', 'libelle' => 'Paiement international', 'famille' => 'international', 'nature' => 'international', 'multi_devises' => true, 'documents_obligatoires' => ['avis_swift', 'justificatif_change']],
            ['code' => 'PAY-AVANCE', 'libelle' => 'Avance', 'famille' => 'echeance', 'nature' => 'avance', 'documents_obligatoires' => ['garantie']],
            ['code' => 'PAY-ACOMPTE', 'libelle' => 'Acompte', 'famille' => 'echeance', 'nature' => 'acompte'],
            ['code' => 'PAY-FINAL', 'libelle' => 'Paiement final', 'famille' => 'standard', 'nature' => 'final', 'documents_obligatoires' => ['preuve_virement']],
            ['code' => 'PAY-REGULARISATION', 'libelle' => 'Régularisation', 'famille' => 'exception', 'nature' => 'regularisation', 'permet_generation' => false, 'permet_creation_manuelle' => true, 'documents_obligatoires' => ['note_explicative', 'decision']],
            ['code' => 'PAY-REMBOURSEMENT', 'libelle' => 'Remboursement', 'famille' => 'exception', 'nature' => 'remboursement', 'documents_obligatoires' => ['decision']],
            ['code' => 'PAY-RETENUE-GARANTIE', 'libelle' => 'Paiement avec retenue de garantie', 'famille' => 'standard', 'nature' => 'retenue_garantie', 'regles_calcul' => ['retenue_garantie' => true]],
            ['code' => 'PAY-MULTI-DEVISES', 'libelle' => 'Paiement multi-devises', 'famille' => 'international', 'nature' => 'multi_devises', 'multi_devises' => true, 'regles_calcul' => ['change' => true], 'documents_obligatoires' => ['justificatif_change']],
        ];
    }
}
