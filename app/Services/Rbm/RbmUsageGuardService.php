<?php

namespace App\Services\Rbm;

use App\Models\Activite;
use App\Models\AxeStrategique;
use App\Models\CadreLogique;
use App\Models\ChaineResultat;
use App\Models\Indicateur;
use App\Models\Pilier;
use App\Models\Produit;
use App\Models\SousProduit;
use App\Models\Tache;
use App\Models\TypeIndicateur;
use App\Models\UniteMesure;
use Illuminate\Database\Eloquent\Model;

class RbmUsageGuardService
{
    public function isInUse(Model $model): bool
    {
        return match ($model::class) {
            Indicateur::class => ChaineResultat::query()
                ->where('indicateur_id', $model->getKey())
                ->exists(),
            CadreLogique::class => ChaineResultat::query()
                ->where('cadre_logique_id', $model->getKey())
                ->exists(),
            default => false,
        };
    }

    public function hasActiveChildren(Model $model): bool
    {
        return match ($model::class) {
            Pilier::class => AxeStrategique::query()->where('pilier_id', $model->getKey())->exists(),
            AxeStrategique::class => Produit::query()->where('axe_strategique_id', $model->getKey())->exists(),
            Produit::class => SousProduit::query()->where('produit_id', $model->getKey())->exists(),
            SousProduit::class => Activite::query()->where('sous_produit_id', $model->getKey())->exists(),
            Activite::class => Tache::query()->where('activite_id', $model->getKey())->exists(),
            Tache::class => $model->indicateurs()->exists(),
            TypeIndicateur::class => Indicateur::query()->where('type_indicateur_id', $model->getKey())->exists(),
            UniteMesure::class => Indicateur::query()->where('unite_mesure_id', $model->getKey())->exists(),
            default => false,
        };
    }
}
