<?php

namespace App\Models;

use App\Models\Concerns\HasRbmReferentialAttributes;
use Database\Factories\ActiviteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Activite extends Model
{
    /** @use HasFactory<ActiviteFactory> */
    use HasFactory;
    use HasRbmReferentialAttributes;
    use SoftDeletes;

    protected $fillable = [
        'sous_produit_id',
        'code',
        'libelle',
        'description',
        'ordre',
        'statut',
        'created_by',
        'updated_by',
    ];

    public function sousProduit(): BelongsTo
    {
        return $this->belongsTo(SousProduit::class);
    }

    public function taches(): HasMany
    {
        return $this->hasMany(Tache::class);
    }

    public function realisationsPhysiques(): HasMany
    {
        return $this->hasMany(SuiviPhysiqueRealisation::class, 'activite_id');
    }
}
