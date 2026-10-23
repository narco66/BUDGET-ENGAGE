<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GedMetadonnee extends Model
{
    use HasFactory;

    protected $table = 'ged_metadonnees';

    protected $fillable = [
        'ged_document_id',
        'cle',
        'valeur',
        'type_valeur',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(GedDocument::class, 'ged_document_id');
    }
}
