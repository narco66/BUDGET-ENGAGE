<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrdonnancementDocument extends Model
{
    use SoftDeletes;

    protected $table = 'ordonnancement_documents';

    protected $fillable = [
        'ordonnancement_id',
        'ged_document_id',
        'categorie',
        'type_piece',
        'source',
        'observation',
        'created_by',
    ];

    public function ordonnancement(): BelongsTo
    {
        return $this->belongsTo(Ordonnancement::class);
    }

    public function gedDocument(): BelongsTo
    {
        return $this->belongsTo(GedDocument::class);
    }

    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeHerites($query)
    {
        return $query->where('categorie', 'herite');
    }

    public function scopeSpecifiques($query)
    {
        return $query->where('categorie', 'specifique');
    }
}
