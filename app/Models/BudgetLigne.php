<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetLigne extends Model
{
    use SoftDeletes;

    protected $table = 'budget_lignes';

    protected $fillable = [
        'exercice_budgetaire_id',
        'version',
        'section',
        'niveau',
        'categorie',
        'code',
        'libelle',
        'parent_code',
        'montant_total',
        'montant_ceeac',
        'montant_ptf',
        'montant_anterieur',
        'montant_realise',
        'montant_ptf_anterieur',
        'montant_ptf_realise',
        'taux_realisation',
        'variation',
        'pap_ligne_budgetaire_id',
        'ligne_budgetaire_id',
        'ordre',
        'source',
        'observation',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'montant_total' => 'decimal:2',
            'montant_ceeac' => 'decimal:2',
            'montant_ptf' => 'decimal:2',
            'montant_anterieur' => 'decimal:2',
            'montant_realise' => 'decimal:2',
            'montant_ptf_anterieur' => 'decimal:2',
            'montant_ptf_realise' => 'decimal:2',
            'taux_realisation' => 'decimal:2',
            'variation' => 'decimal:2',
            'ordre' => 'integer',
        ];
    }

    public function exerciceBudgetaire(): BelongsTo
    {
        return $this->belongsTo(ExerciceBudgetaire::class);
    }

    public function papLigne(): BelongsTo
    {
        return $this->belongsTo(PapLigneBudgetaire::class, 'pap_ligne_budgetaire_id');
    }

    public function ligneBudgetaire(): BelongsTo
    {
        return $this->belongsTo(LigneBudgetaire::class, 'ligne_budgetaire_id');
    }

    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopePourExercice($query, int $exerciceId)
    {
        return $query->where('exercice_budgetaire_id', $exerciceId);
    }

    public function scopeSection($query, string $section)
    {
        return $query->where('section', $section);
    }
}
