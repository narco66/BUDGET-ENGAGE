<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Génère automatiquement un UUID (champ `uuid`) à la création du modèle.
 * L'UUID reste indépendant de la clé primaire (id auto-incrémentée),
 * conformément aux conventions du projet.
 */
trait GeneratesGedUuid
{
    protected static function bootGeneratesGedUuid(): void
    {
        static::creating(function ($model): void {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }
}
