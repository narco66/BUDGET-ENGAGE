<?php

namespace App\Models;

use App\Enums\StatutSignatureGed;
use App\Models\Concerns\GeneratesGedUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GedSignature extends Model
{
    use GeneratesGedUuid;
    use HasFactory;

    protected $table = 'ged_signatures';

    protected $fillable = [
        'uuid',
        'ged_document_id',
        'ged_version_id',
        'signataire_id',
        'demande_par',
        'statut',
        'algorithme',
        'hash_signe',
        'signature_value',
        'certificat',
        'horodatage',
        'motif_rejet',
        'adresse_ip',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'statut' => StatutSignatureGed::class,
            'horodatage' => 'datetime',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(GedDocument::class, 'ged_document_id');
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(GedVersion::class, 'ged_version_id');
    }

    public function signataire(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signataire_id');
    }

    public function demandeur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'demande_par');
    }
}
