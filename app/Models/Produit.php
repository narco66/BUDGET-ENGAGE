<?php

namespace App\Models;

use App\Models\Concerns\HasRbmReferentialAttributes;
use Database\Factories\ProduitFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Produit extends Model
{
    /** @use HasFactory<ProduitFactory> */
    use HasFactory;
    use HasRbmReferentialAttributes;
    use SoftDeletes;

    protected $fillable = [
        'axe_strategique_id',
        'code',
        'libelle',
        'description',
        'ordre',
        'statut',
        'created_by',
        'updated_by',
    ];

    public function axeStrategique(): BelongsTo
    {
        return $this->belongsTo(AxeStrategique::class);
    }

    public function sousProduits(): HasMany
    {
        return $this->hasMany(SousProduit::class);
    }
}
