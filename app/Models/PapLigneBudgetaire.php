<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PapLigneBudgetaire extends Model
{
    use SoftDeletes;

    protected $table = 'pap_lignes_budgetaires';

    protected $fillable = [
        'exercice_budgetaire_id',
        'cadre_logique_id',
        'pilier_id',
        'axe_strategique_id',
        'produit_id',
        'sous_produit_id',
        'code',
        'libelle',
        'niveau',
        'parent_code',
        'montant_total',
        'montant_ceeac',
        'montant_ptf',
        'montant_ceeac_2025',
        'realisation_ceeac_2025',
        'montant_ptf_2025',
        'realisation_ptf_2025',
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
            'montant_ceeac_2025' => 'decimal:2',
            'realisation_ceeac_2025' => 'decimal:2',
            'montant_ptf_2025' => 'decimal:2',
            'realisation_ptf_2025' => 'decimal:2',
            'ordre' => 'integer',
        ];
    }

    public function exerciceBudgetaire(): BelongsTo
    {
        return $this->belongsTo(ExerciceBudgetaire::class);
    }

    public function cadreLogique(): BelongsTo
    {
        return $this->belongsTo(CadreLogique::class);
    }

    public function pilier(): BelongsTo
    {
        return $this->belongsTo(Pilier::class);
    }

    public function axeStrategique(): BelongsTo
    {
        return $this->belongsTo(AxeStrategique::class);
    }

    public function produit(): BelongsTo
    {
        return $this->belongsTo(Produit::class);
    }

    public function sousProduit(): BelongsTo
    {
        return $this->belongsTo(SousProduit::class);
    }

    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lignesBudgetaires(): HasMany
    {
        return $this->hasMany(LigneBudgetaire::class, 'pap_ligne_budgetaire_id');
    }

    public function scopePourExercice($query, int $exerciceId)
    {
        return $query->where('exercice_budgetaire_id', $exerciceId);
    }

    public function scopeNiveau($query, string $niveau)
    {
        return $query->where('niveau', $niveau);
    }
}
