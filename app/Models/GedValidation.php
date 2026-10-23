<?php

namespace App\Models;

use App\Enums\StatutValidationGed;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GedValidation extends Model
{
    use HasFactory;

    protected $table = 'ged_validations';

    protected $fillable = [
        'ged_document_id',
        'ged_workflow_etape_id',
        'ordre',
        'libelle_etape',
        'type_etape',
        'validateur_id',
        'statut',
        'commentaire',
        'motif_rejet',
        'date_action',
    ];

    protected function casts(): array
    {
        return [
            'ordre' => 'integer',
            'statut' => StatutValidationGed::class,
            'date_action' => 'datetime',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(GedDocument::class, 'ged_document_id');
    }

    public function etape(): BelongsTo
    {
        return $this->belongsTo(GedWorkflowEtape::class, 'ged_workflow_etape_id');
    }

    public function validateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validateur_id');
    }
}
