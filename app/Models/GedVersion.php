<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GedVersion extends Model
{
    use HasFactory;

    protected $table = 'ged_versions';

    protected $fillable = [
        'ged_document_id',
        'numero_version',
        'chemin_fichier',
        'nom_original',
        'nom_stocke',
        'extension',
        'mime_type',
        'taille',
        'hash_sha256',
        'commentaire',
        'statut',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'numero_version' => 'integer',
            'taille' => 'integer',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(GedDocument::class, 'ged_document_id');
    }

    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
