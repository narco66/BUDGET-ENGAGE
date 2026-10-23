<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GedArchive extends Model
{
    use HasFactory;

    protected $table = 'ged_archives';

    protected $fillable = [
        'ged_document_id',
        'reference_archive',
        'annee',
        'module',
        'type_document',
        'structure',
        'motif',
        'chemin_archive',
        'hash_sha256',
        'est_verrouille',
        'archive_par',
        'date_archivage',
        'restaure_par',
        'date_restauration',
    ];

    protected function casts(): array
    {
        return [
            'annee' => 'integer',
            'est_verrouille' => 'boolean',
            'date_archivage' => 'datetime',
            'date_restauration' => 'datetime',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(GedDocument::class, 'ged_document_id');
    }

    public function archiveur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'archive_par');
    }

    public function restaurateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restaure_par');
    }
}
