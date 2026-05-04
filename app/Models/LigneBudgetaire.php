<?php

namespace App\Models;

use Database\Factories\LigneBudgetaireFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LigneBudgetaire extends Model
{
    /** @use HasFactory<LigneBudgetaireFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'exercice_budgetaire_id',
        'departement_id',
        'direction_id',
        'service_id',
        'nature_depense_id',
        'source_financement_id',
        'code',
        'libelle',
        'montant_vote',
        'montant_revise',
        'montant_engage',
        'montant_liquide',
        'montant_ordonnance',
        'montant_paye',
        'statut',
    ];

    protected function casts(): array
    {
        return [
            'montant_vote' => 'decimal:2',
            'montant_revise' => 'decimal:2',
            'montant_engage' => 'decimal:2',
            'montant_liquide' => 'decimal:2',
            'montant_ordonnance' => 'decimal:2',
            'montant_paye' => 'decimal:2',
        ];
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

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function natureDepense(): BelongsTo
    {
        return $this->belongsTo(NatureDepense::class);
    }

    public function sourceFinancement(): BelongsTo
    {
        return $this->belongsTo(SourceFinancement::class);
    }

    public function engagements(): HasMany
    {
        return $this->hasMany(Engagement::class);
    }

    public function creditDisponible(): string
    {
        $plafond = (string) ($this->montant_revise !== null && (float) $this->montant_revise > 0
            ? $this->montant_revise
            : $this->montant_vote);

        return bcsub($plafond, (string) $this->montant_engage, 2);
    }
}
