<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GedCorbeille extends Model
{
    use HasFactory;

    protected $table = 'ged_corbeille';

    protected $fillable = [
        'ged_document_id',
        'supprime_par',
        'motif',
        'date_suppression',
        'date_purge_prevue',
        'restaure_par',
        'date_restauration',
    ];

    protected function casts(): array
    {
        return [
            'date_suppression' => 'datetime',
            'date_purge_prevue' => 'date',
            'date_restauration' => 'datetime',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(GedDocument::class, 'ged_document_id');
    }

    public function supprimePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supprime_par');
    }

    public function restaurePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restaure_par');
    }
}
