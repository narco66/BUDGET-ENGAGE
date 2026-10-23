<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PapImport extends Model
{
    protected $table = 'pap_imports';

    protected $fillable = [
        'exercice_budgetaire_id',
        'cadre_logique_id',
        'version',
        'mode',
        'statut',
        'fichier_original',
        'chemin_fichier',
        'checksum',
        'taille',
        'source_label',
        'nb_piliers',
        'nb_axes',
        'nb_produits',
        'nb_sous_produits',
        'nb_lignes',
        'nb_creations',
        'nb_mises_a_jour',
        'nb_suppressions',
        'nb_doublons',
        'nb_avertissements',
        'nb_erreurs',
        'montant_total',
        'rapport',
        'resume',
        'snapshot',
        'restored_from_id',
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
            'montant_total' => 'decimal:2',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function restoredFrom(): BelongsTo
    {
        return $this->belongsTo(PapImport::class, 'restored_from_id');
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
