<?php

namespace App\Models\Concerns;

use App\Models\RbmModificationHistorique;
use App\Models\RbmObservation;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasRbmReferentialAttributes
{
    /**
     * @return array<int, string>
     */
    public static function rbmFillable(): array
    {
        return [
            'code',
            'libelle',
            'description',
            'ordre',
            'statut',
            'created_by',
            'updated_by',
        ];
    }

    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function modificateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function historiques(): MorphMany
    {
        return $this->morphMany(RbmModificationHistorique::class, 'historisable');
    }

    public function observations(): MorphMany
    {
        return $this->morphMany(RbmObservation::class, 'observable');
    }

    public function scopeActifs($query)
    {
        return $query->where('statut', 'actif');
    }

    public function scopeRecherche($query, ?string $terme)
    {
        if ($terme === null || $terme === '') {
            return $query;
        }

        return $query->where(function ($q) use ($terme) {
            $q->where('code', 'like', "%{$terme}%")
                ->orWhere('libelle', 'like', "%{$terme}%");
        });
    }
}
