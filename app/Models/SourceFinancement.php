<?php

namespace App\Models;

use Database\Factories\SourceFinancementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SourceFinancement extends Model
{
    /** @use HasFactory<SourceFinancementFactory> */
    use HasFactory;

    protected $table = 'sources_financement';

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
