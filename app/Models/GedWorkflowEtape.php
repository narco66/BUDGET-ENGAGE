<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GedWorkflowEtape extends Model
{
    use HasFactory;

    protected $table = 'ged_workflow_etapes';

    protected $fillable = [
        'ged_workflow_id',
        'ordre',
        'libelle',
        'type_etape',
        'permission_requise',
        'role_requis',
        'delai_jours',
        'est_obligatoire',
        'notifie',
    ];

    protected function casts(): array
    {
        return [
            'ordre' => 'integer',
            'delai_jours' => 'integer',
            'est_obligatoire' => 'boolean',
            'notifie' => 'boolean',
        ];
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(GedWorkflow::class, 'ged_workflow_id');
    }

    public function validations(): HasMany
    {
        return $this->hasMany(GedValidation::class, 'ged_workflow_etape_id');
    }
}
