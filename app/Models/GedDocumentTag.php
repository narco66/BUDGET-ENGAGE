<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GedDocumentTag extends Model
{
    protected $table = 'ged_document_tags';

    protected $fillable = [
        'ged_document_id',
        'ged_tag_id',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(GedDocument::class, 'ged_document_id');
    }

    public function tag(): BelongsTo
    {
        return $this->belongsTo(GedTag::class, 'ged_tag_id');
    }
}
