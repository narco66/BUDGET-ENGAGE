<?php

namespace App\Models;

use Database\Factories\NatureDepenseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NatureDepense extends Model
{
    /** @use HasFactory<NatureDepenseFactory> */
    use HasFactory;

    protected $table = 'natures_depenses';

    protected $fillable = [
        'code',
        'libelle',
        'description',
        'statut',
    ];

    public function lignesBudgetaires(): HasMany
    {
        return $this->hasMany(LigneBudgetaire::class);
    }
}
