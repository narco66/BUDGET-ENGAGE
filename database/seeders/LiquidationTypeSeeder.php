<?php

namespace Database\Seeders;

use App\Models\LiquidationType;
use Illuminate\Database\Seeder;

class LiquidationTypeSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->types() as $ordre => $type) {
            LiquidationType::query()->updateOrCreate(
                ['code' => $type['code']],
                array_merge($type, ['ordre' => $ordre]),
            );
        }
    }

    /**
     * Référentiel administrable des types de liquidation.
     *
     * @return array<int, array<string, mixed>>
     */
    private function types(): array
    {
        $champsRetenues = ['retenues' => ['label' => 'Retenues', 'type' => 'decimal']];
        $champsPenalites = ['penalites' => ['label' => 'Pénalités', 'type' => 'decimal']];
        $champsGarantie = ['retenue_garantie' => ['label' => 'Retenue de garantie', 'type' => 'decimal']];
        $champsRevision = ['revision_prix' => ['label' => 'Révision de prix', 'type' => 'decimal']];

        return [
            [
                'code' => 'LIQ-TOTALE',
                'libelle' => 'Liquidation totale',
                'famille' => 'standard',
                'nature' => 'totale',
                'documents_obligatoires' => ['facture', 'attestation_service_fait'],
                'exige_service_fait' => true,
            ],
            [
                'code' => 'LIQ-PARTIELLE',
                'libelle' => 'Liquidation partielle',
                'famille' => 'standard',
                'nature' => 'partielle',
                'documents_obligatoires' => ['facture', 'attestation_service_fait'],
                'exige_service_fait' => true,
            ],
            [
                'code' => 'LIQ-MARCHE',
                'libelle' => 'Liquidation de marché',
                'famille' => 'commande_publique',
                'nature' => 'marche',
                'documents_obligatoires' => ['marche', 'decompte', 'pv_reception'],
                'exige_service_fait' => true,
                'exige_reception' => true,
            ],
            [
                'code' => 'LIQ-CONTRAT',
                'libelle' => 'Liquidation de contrat',
                'famille' => 'commande_publique',
                'nature' => 'contrat',
                'documents_obligatoires' => ['contrat', 'facture'],
                'exige_service_fait' => true,
            ],
            [
                'code' => 'LIQ-CONVENTION',
                'libelle' => 'Liquidation de convention',
                'famille' => 'commande_publique',
                'nature' => 'convention',
                'documents_obligatoires' => ['convention', 'rapport_technique'],
                'exige_service_fait' => true,
            ],
            [
                'code' => 'LIQ-MISSION',
                'libelle' => 'Liquidation de mission',
                'famille' => 'fonctionnement',
                'nature' => 'mission',
                'documents_obligatoires' => ['ordre_mission', 'rapport_mission'],
                'exige_service_fait' => true,
            ],
            [
                'code' => 'LIQ-ACOMPTE',
                'libelle' => 'Liquidation d’acompte',
                'famille' => 'echeance',
                'nature' => 'acompte',
                'documents_obligatoires' => ['facture'],
                'exige_service_fait' => false,
            ],
            [
                'code' => 'LIQ-AVANCE',
                'libelle' => 'Liquidation d’avance',
                'famille' => 'echeance',
                'nature' => 'avance',
                'documents_obligatoires' => ['demande_avance', 'garantie'],
                'exige_service_fait' => false,
            ],
            [
                'code' => 'LIQ-FINALE',
                'libelle' => 'Liquidation finale',
                'famille' => 'standard',
                'nature' => 'finale',
                'documents_obligatoires' => ['decompte_general_definitif', 'pv_reception'],
                'exige_service_fait' => true,
                'exige_reception' => true,
            ],
            [
                'code' => 'LIQ-REGULARISATION',
                'libelle' => 'Liquidation de régularisation',
                'famille' => 'exception',
                'nature' => 'regularisation',
                'documents_obligatoires' => ['note_explicative', 'decision'],
                'permet_generation' => false,
                'permet_creation_manuelle' => true,
                'exige_service_fait' => false,
            ],
            [
                'code' => 'LIQ-REVISION-PRIX',
                'libelle' => 'Liquidation avec révision de prix',
                'famille' => 'standard',
                'nature' => 'revision_prix',
                'champs_specifiques' => $champsRevision,
                'regles_calcul' => ['revision_prix' => true],
                'documents_obligatoires' => ['facture', 'formule_revision'],
                'exige_service_fait' => true,
            ],
            [
                'code' => 'LIQ-PENALITES',
                'libelle' => 'Liquidation avec pénalités',
                'famille' => 'standard',
                'nature' => 'penalites',
                'champs_specifiques' => $champsPenalites,
                'regles_calcul' => ['penalites' => true],
                'documents_obligatoires' => ['facture', 'decompte_penalites'],
                'exige_service_fait' => true,
            ],
            [
                'code' => 'LIQ-RETENUE-GARANTIE',
                'libelle' => 'Liquidation avec retenue de garantie',
                'famille' => 'standard',
                'nature' => 'retenue_garantie',
                'champs_specifiques' => array_merge($champsRetenues, $champsGarantie),
                'regles_calcul' => ['retenue_garantie' => true],
                'documents_obligatoires' => ['facture', 'pv_reception'],
                'exige_service_fait' => true,
            ],
            [
                'code' => 'LIQ-CORRECTIVE',
                'libelle' => 'Liquidation corrective',
                'famille' => 'exception',
                'nature' => 'corrective',
                'documents_obligatoires' => ['note_explicative', 'decision'],
                'permet_generation' => false,
                'permet_creation_manuelle' => true,
                'exige_service_fait' => false,
            ],
        ];
    }
}
