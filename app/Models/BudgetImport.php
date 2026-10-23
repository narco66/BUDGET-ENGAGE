<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetImport extends Model
{
    protected $table = 'budget_imports';

    protected $fillable = [
        'exercice_budgetaire_id',
        'version',
        'mode',
        'statut',
        'fichier_original',
        'chemin_fichier',
        'checksum',
        'taille',
        'source_label',
        'nb_titres',
        'nb_chapitres',
        'nb_articles',
        'nb_paragraphes',
        'nb_lignes',
        'nb_recettes',
        'nb_depenses',
        'nb_creations',
        'nb_mises_a_jour',
        'nb_suppressions',
        'nb_doublons',
        'nb_avertissements',
        'nb_erreurs',
        'montant_recettes',
        'montant_depenses',
        'montant_ceeac',
        'montant_ptf',
        'rapport',
        'resume',
        'snapshot',
        'restored_from_id',
        'legacy_import_budget_id',
        'user_id',
        'adresse_ip',
        'user_agent',
        'started_at',
        'finished_at',
        'duree_ms',
    ];

    protected function casts(): array
    {
        return [
            'rapport' => 'array',
            'resume' => 'array',
            'montant_recettes' => 'decimal:2',
            'montant_depenses' => 'decimal:2',
            'montant_ceeac' => 'decimal:2',
            'montant_ptf' => 'decimal:2',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function exerciceBudgetaire(): BelongsTo
    {
        return $this->belongsTo(ExerciceBudgetaire::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function restoredFrom(): BelongsTo
    {
        return $this->belongsTo(BudgetImport::class, 'restored_from_id');
    }

    public function legacyImport(): BelongsTo
    {
        return $this->belongsTo(ImportBudget::class, 'legacy_import_budget_id');
    }

    public function isLegacyCsv(): bool
    {
        return $this->mode === 'legacy_csv';
    }

    /**
     * @return array<string, mixed>
     */
    public function decodedSnapshot(): array
    {
        if ($this->snapshot === null || $this->snapshot === '') {
            return [];
        }

        return json_decode($this->snapshot, true) ?: [];
    }
}
