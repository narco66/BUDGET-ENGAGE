<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GedWorkflow extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'ged_workflows';

    protected $fillable = [
        'code',
        'libelle',
        'description',
        'type_document_id',
        'est_actif',
        'est_par_defaut',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'est_actif' => 'boolean',
            'est_par_defaut' => 'boolean',
        ];
    }

    public function typeDocument(): BelongsTo
    {
        return $this->belongsTo(GedTypeDocument::class, 'type_document_id');
    }

    public function etapes(): HasMany
    {
        return $this->hasMany(GedWorkflowEtape::class, 'ged_workflow_id')->orderBy('ordre');
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
