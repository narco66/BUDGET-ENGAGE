<?php

namespace App\Models;

use App\Enums\StatutSuiviRealisation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SuiviPhysiqueRealisation extends Model
{
    use SoftDeletes;

    protected $table = 'suivi_physique_realisations';

    protected $fillable = [
        'activite_id',
        'exercice_budgetaire_id',
        'trimestre',
        'annee',
        'periode_debut',
        'periode_fin',
        'taux_avancement',
        'quantite_realisee',
        'libelle_realisation',
        'statut',
        'commentaire',
        'created_by',
        'validated_by',
        'validated_at',
    ];

    protected function casts(): array
    {
        return [
            'periode_debut' => 'date',
            'periode_fin' => 'date',
            'taux_avancement' => 'decimal:2',
            'quantite_realisee' => 'decimal:4',
            'statut' => StatutSuiviRealisation::class,
            'validated_at' => 'datetime',
        ];
    }

    public function activite(): BelongsTo
    {
        return $this->belongsTo(Activite::class);
    }

    public function exerciceBudgetaire(): BelongsTo
    {
        return $this->belongsTo(ExerciceBudgetaire::class);
    }

    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function validateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }
}
