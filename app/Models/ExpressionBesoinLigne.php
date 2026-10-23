<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpressionBesoinLigne extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'expression_besoin_lignes';

    protected $fillable = [
        'expression_besoin_id',
        'designation',
        'code_article',
        'categorie',
        'specifications',
        'quantite',
        'unite',
        'prix_unitaire',
        'montant',
        'priorite',
        'echeance',
        'ligne_budgetaire_id',
        'source_financement_id',
        'activite_id',
        'observation',
    ];

    protected function casts(): array
    {
        return [
            'quantite' => 'decimal:3',
            'prix_unitaire' => 'decimal:2',
            'montant' => 'decimal:2',
            'echeance' => 'date',
        ];
    }

    public function expressionBesoin(): BelongsTo
    {
        return $this->belongsTo(ExpressionBesoin::class);
    }

    public function ligneBudgetaire(): BelongsTo
    {
        return $this->belongsTo(LigneBudgetaire::class);
    }

    public function sourceFinancement(): BelongsTo
    {
        return $this->belongsTo(SourceFinancement::class);
    }

    public function activite(): BelongsTo
    {
        return $this->belongsTo(Activite::class);
    }
}
