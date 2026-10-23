<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GedNumerisation extends Model
{
    use HasFactory;

    protected $table = 'ged_numerisations';

    protected $fillable = [
        'ged_document_id',
        'lot_reference',
        'source',
        'nb_pages',
        'resolution_dpi',
        'format',
        'ocr_status',
        'ocr_contenu',
        'chemin_fichier',
        'numerise_par',
        'date_numerisation',
    ];

    protected function casts(): array
    {
        return [
            'nb_pages' => 'integer',
            'resolution_dpi' => 'integer',
            'date_numerisation' => 'datetime',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(GedDocument::class, 'ged_document_id');
    }

    public function numerisePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'numerise_par');
    }
}
