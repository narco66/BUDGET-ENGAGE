<?php

namespace App\Models;

use App\Models\Concerns\GeneratesGedUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GedDossier extends Model
{
    use GeneratesGedUuid;
    use HasFactory;
    use SoftDeletes;

    protected $table = 'ged_dossiers';

    protected $fillable = [
        'uuid',
        'reference',
        'code',
        'libelle',
        'description',
        'parent_id',
        'categorie_id',
        'exercice_budgetaire_id',
        'departement_id',
        'direction_id',
        'service_id',
        'module_source',
        'statut',
        'created_by',
        'updated_by',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function enfants(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function categorie(): BelongsTo
    {
        return $this->belongsTo(GedCategorie::class, 'categorie_id');
    }

    public function exerciceBudgetaire(): BelongsTo
    {
        return $this->belongsTo(ExerciceBudgetaire::class);
    }

    public function departement(): BelongsTo
    {
        return $this->belongsTo(Departement::class);
    }

    public function direction(): BelongsTo
    {
        return $this->belongsTo(Direction::class);
    }

    public function serviceStructure(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(GedDocument::class, 'dossier_id');
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
