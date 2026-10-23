<?php

namespace App\Models;

use App\Models\Concerns\HasRbmReferentialAttributes;
use Database\Factories\CadreLogiqueFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CadreLogique extends Model
{
    /** @use HasFactory<CadreLogiqueFactory> */
    use HasFactory;
    use HasRbmReferentialAttributes;
    use SoftDeletes;

    protected $table = 'cadres_logiques';

    protected $fillable = [
        'code',
        'libelle',
        'description',
        'annee',
        'version',
        'ordre',
        'statut',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'annee' => 'integer',
        ];
    }

    public function elements(): HasMany
    {
        return $this->hasMany(CadreLogiqueElement::class)->orderBy('ordre');
    }

    public function chainesResultats(): HasMany
    {
        return $this->hasMany(ChaineResultat::class);
    }
}
