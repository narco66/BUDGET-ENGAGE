<?php

namespace App\Models;

use App\Enums\NiveauRisqueAlerte;
use App\Enums\StatutTraitementAlerte;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlerteAudit extends Model
{
    protected $table = 'alertes_audit';

    protected $fillable = [
        'engagement_id',
        'code_alerte',
        'type_alerte',
        'niveau_risque',
        'score_risque',
        'description',
        'statut_traitement',
        'detectee_le',
        'traitee_par',
        'commentaire_traitement',
    ];

    protected function casts(): array
    {
        return [
            'niveau_risque' => NiveauRisqueAlerte::class,
            'statut_traitement' => StatutTraitementAlerte::class,
            'detectee_le' => 'datetime',
        ];
    }

    public function engagement(): BelongsTo
    {
        return $this->belongsTo(Engagement::class);
    }

    public function traitePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'traitee_par');
    }
}
