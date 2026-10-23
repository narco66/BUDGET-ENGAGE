<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LiquidationType extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'liquidation_types';

    protected $fillable = [
        'code',
        'libelle',
        'description',
        'famille',
        'nature',
        'champs_specifiques',
        'documents_obligatoires',
        'regles_calcul',
        'workflow_code',
        'permet_generation',
        'permet_creation_manuelle',
        'exige_service_fait',
        'exige_reception',
        'est_actif',
        'ordre',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'champs_specifiques' => 'array',
            'documents_obligatoires' => 'array',
            'regles_calcul' => 'array',
            'permet_generation' => 'boolean',
            'permet_creation_manuelle' => 'boolean',
            'exige_service_fait' => 'boolean',
            'exige_reception' => 'boolean',
            'est_actif' => 'boolean',
            'ordre' => 'integer',
        ];
    }

    public function liquidations(): HasMany
    {
        return $this->hasMany(Liquidation::class, 'liquidation_type_id');
    }

    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function modificateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeActifs($query)
    {
        return $query->where('est_actif', true);
    }
}
