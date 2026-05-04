<?php

namespace App\Models;

use Database\Factories\DirectionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Direction extends Model
{
    /** @use HasFactory<DirectionFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'departement_id',
        'code',
        'libelle',
        'description',
        'statut',
    ];

    public function departement(): BelongsTo
    {
        return $this->belongsTo(Departement::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function engagements(): HasMany
    {
        return $this->hasMany(Engagement::class);
    }
}
