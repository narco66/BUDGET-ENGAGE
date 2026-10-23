<?php

namespace App\Models;

use App\Enums\SortFinalConservationGed;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GedConservationRule extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'ged_conservation_rules';

    protected $fillable = [
        'code',
        'libelle',
        'description',
        'type_document_id',
        'duree_conservation_mois',
        'sort_final',
        'declenchement',
        'alerte_avant_jours',
        'conservation_obligatoire',
        'est_active',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'duree_conservation_mois' => 'integer',
            'alerte_avant_jours' => 'integer',
            'conservation_obligatoire' => 'boolean',
            'est_active' => 'boolean',
            'sort_final' => SortFinalConservationGed::class,
        ];
    }

    public function typeDocument(): BelongsTo
    {
        return $this->belongsTo(GedTypeDocument::class, 'type_document_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(GedDocument::class, 'conservation_rule_id');
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
