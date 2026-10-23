<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CadreLogiqueElement extends Model
{
    protected $table = 'cadre_logique_elements';

    protected $fillable = [
        'cadre_logique_id',
        'niveau',
        'element_type',
        'element_id',
        'ordre',
        'hypotheses',
        'resultat_attendu',
    ];

    public function cadreLogique(): BelongsTo
    {
        return $this->belongsTo(CadreLogique::class);
    }

    public function element(): MorphTo
    {
        return $this->morphTo();
    }
}
