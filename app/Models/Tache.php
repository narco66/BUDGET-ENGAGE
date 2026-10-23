<?php

namespace App\Models;

use App\Models\Concerns\HasRbmReferentialAttributes;
use Database\Factories\TacheFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tache extends Model
{
    /** @use HasFactory<TacheFactory> */
    use HasFactory;
    use HasRbmReferentialAttributes;
    use SoftDeletes;

    protected $fillable = [
        'activite_id',
        'code',
        'libelle',
        'description',
        'ordre',
        'statut',
        'created_by',
        'updated_by',
    ];

    public function activite(): BelongsTo
    {
        return $this->belongsTo(Activite::class);
    }

    public function indicateurs(): BelongsToMany
    {
        return $this->belongsToMany(Indicateur::class, 'indicateur_tache')->withTimestamps();
    }
}
