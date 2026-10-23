<?php

namespace App\Models;

use App\Models\Concerns\HasRbmReferentialAttributes;
use Database\Factories\TypeIndicateurFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TypeIndicateur extends Model
{
    /** @use HasFactory<TypeIndicateurFactory> */
    use HasFactory;
    use HasRbmReferentialAttributes;
    use SoftDeletes;

    protected $table = 'types_indicateurs';

    protected $fillable = [
        'code',
        'libelle',
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
