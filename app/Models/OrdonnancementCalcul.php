<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrdonnancementCalcul extends Model
{
    protected $table = 'ordonnancement_calculs';

    protected $fillable = [
        'ordonnancement_id',
        'ordonnancement_ligne_id',
        'type_calcul',
        'formule',
        'entrees',
        'resultat',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'entrees' => 'array',
            'resultat' => 'decimal:2',
        ];
    }

    public function ordonnancement(): BelongsTo
    {
        return $this->belongsTo(Ordonnancement::class);
    }

    public function ligne(): BelongsTo
    {
        return $this->belongsTo(OrdonnancementLigne::class, 'ordonnancement_ligne_id');
    }

    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
