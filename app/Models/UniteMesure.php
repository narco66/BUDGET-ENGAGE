<?php

namespace App\Models;

use App\Models\Concerns\HasRbmReferentialAttributes;
use Database\Factories\UniteMesureFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class UniteMesure extends Model
{
    /** @use HasFactory<UniteMesureFactory> */
    use HasFactory;
    use HasRbmReferentialAttributes;
    use SoftDeletes;

    protected $table = 'unites_mesure';

    protected $fillable = [
        'code',
        'libelle',
        'symbole',
        'description',
        'ordre',
        'statut',
        'created_by',
        'updated_by',
    ];

    public function indicateurs(): HasMany
    {
        return $this->hasMany(Indicateur::class);
    }
}
