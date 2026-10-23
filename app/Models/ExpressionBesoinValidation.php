<?php

namespace App\Models;

use App\Enums\StatutValidationGed;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpressionBesoinValidation extends Model
{
    use HasFactory;

    protected $table = 'expression_besoin_validations';

    protected $fillable = [
        'expression_besoin_id',
        'ordre',
        'libelle_etape',
        'type_etape',
        'permission_requise',
        'role_requis',
        'validateur_id',
        'statut',
        'commentaire',
        'motif_rejet',
        'date_action',
    ];

    protected function casts(): array
    {
        return [
            'ordre' => 'integer',
            'statut' => StatutValidationGed::class,
            'date_action' => 'datetime',
        ];
    }

    public function expressionBesoin(): BelongsTo
    {
        return $this->belongsTo(ExpressionBesoin::class);
    }

    public function validateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validateur_id');
    }
}
