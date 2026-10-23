<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EngagementType extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'engagement_types';

    protected $fillable = [
        'code',
        'libelle',
        'description',
        'famille',
        'nature',
        'champs_specifiques',
        'documents_obligatoires',
        'workflow_code',
        'permet_generation',
        'permet_creation_manuelle',
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
            'permet_generation' => 'boolean',
            'permet_creation_manuelle' => 'boolean',
            'est_actif' => 'boolean',
            'ordre' => 'integer',
        ];
    }

    public function engagements(): HasMany
    {
        return $this->hasMany(Engagement::class, 'engagement_type_id');
    }

    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function modificateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
