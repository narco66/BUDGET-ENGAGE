<?php

namespace App\Models;

use App\Models\Concerns\GeneratesGedUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GedPartage extends Model
{
    use GeneratesGedUuid;
    use HasFactory;

    protected $table = 'ged_partages';

    protected $fillable = [
        'uuid',
        'ged_document_id',
        'partage_par',
        'partage_avec_user_id',
        'partage_avec_role',
        'niveau_acces',
        'token',
        'date_expiration',
        'est_actif',
    ];

    protected function casts(): array
    {
        return [
            'date_expiration' => 'datetime',
            'est_actif' => 'boolean',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(GedDocument::class, 'ged_document_id');
    }

    public function partagePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'partage_par');
    }

    public function partageAvec(): BelongsTo
    {
        return $this->belongsTo(User::class, 'partage_avec_user_id');
    }
}
