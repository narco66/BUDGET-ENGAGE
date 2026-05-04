<?php

namespace App\Models;

use Database\Factories\DepartementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Departement extends Model
{
    /** @use HasFactory<DepartementFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'code',
        'libelle',
        'description',
        'statut',
    ];

    public function directions(): HasMany
    {
        return $this->hasMany(Direction::class);
    }

    public function lignesBudgetaires(): HasMany
    {
        return $this->hasMany(LigneBudgetaire::class);
    }

    public function engagements(): HasMany
    {
        return $this->hasMany(Engagement::class);
    }
}
