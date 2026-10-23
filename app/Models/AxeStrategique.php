<?php

namespace App\Models;

use App\Models\Concerns\HasRbmReferentialAttributes;
use Database\Factories\AxeStrategiqueFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AxeStrategique extends Model
{
    /** @use HasFactory<AxeStrategiqueFactory> */
    use HasFactory;
    use HasRbmReferentialAttributes;
    use SoftDeletes;

    protected $table = 'axes_strategiques';

    protected $fillable = [
        'pilier_id',
        'code',
        'libelle',
        'description',
        'ordre',
        'statut',
        'created_by',
        'updated_by',
    ];

    public function pilier(): BelongsTo
    {
        return $this->belongsTo(Pilier::class);
    }

    public function produits(): HasMany
    {
        return $this->hasMany(Produit::class);
    }
}
