<?php

namespace App\Services\Engagement;

use App\Models\Engagement;
use App\Models\EngagementType;

/**
 * Pilotage des champs spécifiques (EAV) d'un engagement selon son type.
 * Persiste uniquement les clés définies par le type et signale les champs
 * obligatoires manquants. Aucune modification du schéma de base.
 */
class EngagementTypeService
{
    /**
     * Synchronise les valeurs spécifiques d'un engagement avec les champs
     * définis par son type. Les clés non définies par le type sont supprimées.
     *
     * @param  array<string, mixed>  $valeurs
     */
    public function syncTypeValeurs(Engagement $engagement, ?int $typeId, array $valeurs): void
    {
        $type = $typeId !== null ? EngagementType::query()->find($typeId) : null;

        if ($type === null) {
            $engagement->typeValeurs()->delete();

            return;
        }

        $definitions = collect($type->champs_specifiques ?? []);
        $cles = $definitions->pluck('cle')->filter()->values()->all();

        $engagement->typeValeurs()->whereNotIn('cle', $cles !== [] ? $cles : ['__aucune__'])->delete();

        foreach ($definitions as $definition) {
            $cle = $definition['cle'] ?? null;
            if (! is_string($cle) || $cle === '') {
                continue;
            }

            $valeur = $valeurs[$cle] ?? null;

            $engagement->typeValeurs()->updateOrCreate(
                ['cle' => $cle],
                ['valeur' => ($valeur === '' ? null : $valeur), 'type_valeur' => $definition['type'] ?? 'string'],
            );
        }
    }

    /**
     * Retourne les libellés des champs obligatoires non renseignés pour le type.
     *
     * @param  array<string, mixed>  $valeurs
     * @return array<int, string>
     */
    public function missingRequiredFields(?EngagementType $type, array $valeurs): array
    {
        if ($type === null) {
            return [];
        }

        $manquants = [];
        foreach ($type->champs_specifiques ?? [] as $definition) {
            if (empty($definition['requis'])) {
                continue;
            }
            $cle = $definition['cle'] ?? null;
            if (is_string($cle) && $cle !== '' && blank($valeurs[$cle] ?? null)) {
                $manquants[] = $definition['libelle'] ?? $cle;
            }
        }

        return $manquants;
    }
}
