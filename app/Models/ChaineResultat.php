<?php

namespace App\Models;

use App\Models\Concerns\HasRbmReferentialAttributes;
use Database\Factories\ChaineResultatFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChaineResultat extends Model
{
    /** @use HasFactory<ChaineResultatFactory> */
    use HasFactory;
    use HasRbmReferentialAttributes;
    use SoftDeletes;

    protected $table = 'chaines_resultats';

    protected $fillable = [
        'cadre_logique_id',
        'code',
        'libelle',
        'description',
        'niveau',
        'element_type',
        'element_id',
        'indicateur_id',
        'resultat_attendu',
        'ordre',
        'statut',
        'created_by',
        'updated_by',
    ];

    public function cadreLogique(): BelongsTo
    {
        return $this->belongsTo(CadreLogique::class);
    }

    public function element(): MorphTo
    {
        return $this->morphTo();
    }

    public function indicateur(): BelongsTo
    {
        return $this->belongsTo(Indicateur::class);
    }
}
