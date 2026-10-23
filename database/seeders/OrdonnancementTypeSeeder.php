<?php

namespace Database\Seeders;

use App\Models\OrdonnancementType;
use Illuminate\Database\Seeder;

class OrdonnancementTypeSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->types() as $ordre => $type) {
            OrdonnancementType::query()->updateOrCreate(
                ['code' => $type['code']],
                array_merge($type, ['ordre' => $ordre]),
            );
        }
    }

    /**
     * Référentiel administrable des types d'ordonnancement.
     *
     * @return array<int, array<string, mixed>>
     */
    private function types(): array
    {
        return [
            ['code' => 'ORD-TOTAL', 'libelle' => 'Ordonnancement total', 'famille' => 'standard', 'nature' => 'total', 'documents_obligatoires' => ['ordre_paiement', 'mandat']],
            ['code' => 'ORD-PARTIEL', 'libelle' => 'Ordonnancement partiel', 'famille' => 'standard', 'nature' => 'partiel', 'documents_obligatoires' => ['ordre_paiement']],
            ['code' => 'ORD-MARCHE', 'libelle' => 'Ordonnancement de marché', 'famille' => 'commande_publique', 'nature' => 'marche', 'documents_obligatoires' => ['mandat', 'visa_financier']],
            ['code' => 'ORD-CONTRAT', 'libelle' => 'Ordonnancement de contrat', 'famille' => 'commande_publique', 'nature' => 'contrat', 'documents_obligatoires' => ['mandat']],
            ['code' => 'ORD-CONVENTION', 'libelle' => 'Ordonnancement de convention', 'famille' => 'commande_publique', 'nature' => 'convention', 'documents_obligatoires' => ['mandat']],
            ['code' => 'ORD-MISSION', 'libelle' => 'Ordonnancement de mission', 'famille' => 'fonctionnement', 'nature' => 'mission', 'documents_obligatoires' => ['ordre_paiement']],
            ['code' => 'ORD-AVANCE', 'libelle' => 'Ordonnancement d’avance', 'famille' => 'echeance', 'nature' => 'avance', 'documents_obligatoires' => ['decision', 'garantie']],
            ['code' => 'ORD-REGULARISATION', 'libelle' => 'Ordonnancement de régularisation', 'famille' => 'exception', 'nature' => 'regularisation', 'permet_generation' => false, 'permet_creation_manuelle' => true, 'documents_obligatoires' => ['note_explicative', 'decision']],
            ['code' => 'ORD-RECTIFICATIF', 'libelle' => 'Ordonnancement rectificatif', 'famille' => 'exception', 'nature' => 'rectificatif', 'permet_generation' => false, 'permet_creation_manuelle' => true, 'documents_obligatoires' => ['note_explicative']],
            ['code' => 'ORD-ANNULATION', 'libelle' => 'Ordonnancement d’annulation', 'famille' => 'exception', 'nature' => 'annulation', 'permet_generation' => false, 'permet_creation_manuelle' => true, 'exige_mandat' => false, 'documents_obligatoires' => ['decision']],
            ['code' => 'ORD-CLOTURE', 'libelle' => 'Ordonnancement de clôture', 'famille' => 'standard', 'nature' => 'cloture', 'documents_obligatoires' => ['decompte_general_definitif', 'mandat']],
        ];
    }
}
