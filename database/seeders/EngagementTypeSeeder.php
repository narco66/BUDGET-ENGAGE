<?php

namespace Database\Seeders;

use App\Models\EngagementType;
use Illuminate\Database\Seeder;

/**
 * Référentiel initial des 12 types d'engagement (idempotent via firstOrCreate).
 * `champs_specifiques` décrit le formulaire dynamique (EAV) de chaque type ;
 * `documents_obligatoires` les pièces requises.
 */
class EngagementTypeSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->types() as $ordre => $type) {
            EngagementType::query()->firstOrCreate(
                ['code' => $type['code']],
                array_merge($type, ['ordre' => $ordre + 1, 'est_actif' => true]),
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function types(): array
    {
        $champ = fn (string $cle, string $libelle, string $type = 'text', bool $requis = false): array => compact('cle', 'libelle', 'type', 'requis');

        return [
            ['code' => 'ADM', 'libelle' => 'Engagement administratif', 'famille' => 'administratif', 'champs_specifiques' => [], 'documents_obligatoires' => ['note_service']],
            ['code' => 'JUR', 'libelle' => 'Engagement juridique', 'famille' => 'juridique', 'champs_specifiques' => [
                $champ('contrat', 'Référence du contrat', 'text', true),
                $champ('marche', 'Numéro de marché'),
                $champ('convention', 'Convention'),
                $champ('garanties', 'Garanties', 'textarea'),
                $champ('penalites', 'Pénalités', 'textarea'),
                $champ('duree', 'Durée (mois)', 'number'),
            ], 'documents_obligatoires' => ['contrat', 'marche']],
            ['code' => 'CPT', 'libelle' => 'Engagement comptable', 'famille' => 'comptable', 'champs_specifiques' => [
                $champ('certificat', 'Référence du certificat', 'text'),
                $champ('visa', 'Visa', 'text'),
            ], 'documents_obligatoires' => ['certificat']],
            ['code' => 'PLU', 'libelle' => 'Engagement pluriannuel', 'famille' => 'pluriannuel', 'champs_specifiques' => [
                $champ('annee_debut', 'Année de début', 'number', true),
                $champ('annee_fin', 'Année de fin', 'number', true),
                $champ('ventilation', 'Ventilation annuelle', 'textarea'),
                $champ('autorisation', 'Autorisation d\'engagement', 'text'),
            ], 'documents_obligatoires' => ['autorisation']],
            ['code' => 'PRO', 'libelle' => 'Engagement provisionnel', 'famille' => 'comptable', 'champs_specifiques' => [
                $champ('motif_provision', 'Motif de la provision', 'textarea', true),
            ], 'documents_obligatoires' => []],
            ['code' => 'COMP', 'libelle' => 'Engagement complémentaire', 'famille' => 'comptable', 'champs_specifiques' => [
                $champ('engagement_initial', 'Engagement initial (numéro)', 'text', true),
            ], 'documents_obligatoires' => []],
            ['code' => 'RECT', 'libelle' => 'Engagement rectificatif', 'famille' => 'comptable', 'champs_specifiques' => [
                $champ('engagement_initial', 'Engagement initial (numéro)', 'text', true),
                $champ('motif_rectification', 'Motif de rectification', 'textarea', true),
            ], 'documents_obligatoires' => []],
            ['code' => 'AVEN', 'libelle' => 'Engagement modificatif (avenant)', 'famille' => 'juridique', 'champs_specifiques' => [
                $champ('engagement_initial', 'Engagement initial (numéro)', 'text', true),
                $champ('numero_avenant', 'Numéro d\'avenant', 'text', true),
                $champ('motif_avenant', 'Motif de l\'avenant', 'textarea', true),
            ], 'documents_obligatoires' => ['avenant']],
            ['code' => 'ANNUL', 'libelle' => 'Engagement d\'annulation', 'famille' => 'comptable', 'permet_generation' => false, 'champs_specifiques' => [
                $champ('engagement_annule', 'Engagement annulé (numéro)', 'text', true),
                $champ('motif_annulation', 'Motif d\'annulation', 'textarea', true),
            ], 'documents_obligatoires' => ['decision']],
            ['code' => 'REG', 'libelle' => 'Engagement de régularisation', 'famille' => 'comptable', 'champs_specifiques' => [
                $champ('motif_regularisation', 'Motif de régularisation', 'textarea', true),
            ], 'documents_obligatoires' => []],
            ['code' => 'INT', 'libelle' => 'Engagement interne', 'famille' => 'administratif', 'champs_specifiques' => [], 'documents_obligatoires' => []],
            ['code' => 'EXT', 'libelle' => 'Engagement externe', 'famille' => 'externe', 'champs_specifiques' => [
                $champ('reference_externe', 'Référence externe', 'text'),
                $champ('partenaire', 'Partenaire / bailleur', 'text'),
            ], 'documents_obligatoires' => ['convention']],
        ];
    }
}
