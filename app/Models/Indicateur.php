<?php

namespace App\Models;

use App\Models\Concerns\HasRbmReferentialAttributes;
use Database\Factories\IndicateurFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Indicateur extends Model
{
    /** @use HasFactory<IndicateurFactory> */
    use HasFactory;
    use HasRbmReferentialAttributes;
    use SoftDeletes;

    protected $fillable = [
        'type_indicateur_id',
        'unite_mesure_id',
        'code',
        'libelle',
        'description',
        'ordre',
        'statut',
        'created_by',
        'updated_by',
    ];

    public function typeIndicateur(): BelongsTo
    {
        return $this->belongsTo(TypeIndicateur::class);
    }

    public function uniteMesure(): BelongsTo
    {
        return $this->belongsTo(UniteMesure::class);
    }

    public function taches(): BelongsToMany
    {
        return $this->belongsToMany(Tache::class, 'indicateur_tache')->withTimestamps();
    }

    public function sourcesVerification(): BelongsToMany
    {
        return $this->belongsToMany(SourceVerification::class, 'indicateur_source_verification')->withTimestamps();
    }

    public function chainesResultats(): HasMany
    {
        return $this->hasMany(ChaineResultat::class);
    }

    public function realisations(): HasMany
    {
        return $this->hasMany(IndicateurRealisation::class);
    }
}
