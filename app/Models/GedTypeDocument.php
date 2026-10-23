<?php

namespace App\Models;

use App\Enums\NiveauConfidentialiteGed;
use App\Enums\SortFinalConservationGed;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GedTypeDocument extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'ged_types_documents';

    protected $fillable = [
        'code',
        'libelle',
        'description',
        'categorie_id',
        'prefixe_reference',
        'duree_conservation_mois',
        'sort_final',
        'niveau_confidentialite_defaut',
        'est_signable',
        'extensions_autorisees',
        'taille_max_ko',
        'ordre',
        'statut',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'duree_conservation_mois' => 'integer',
            'taille_max_ko' => 'integer',
            'ordre' => 'integer',
            'est_signable' => 'boolean',
            'extensions_autorisees' => 'array',
            'sort_final' => SortFinalConservationGed::class,
            'niveau_confidentialite_defaut' => NiveauConfidentialiteGed::class,
        ];
    }

    public function categorie(): BelongsTo
    {
        return $this->belongsTo(GedCategorie::class, 'categorie_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(GedDocument::class, 'type_document_id');
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
