<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class GedDocumentAttachment extends Model
{
    use HasFactory;

    protected $table = 'ged_document_attachments';

    protected $fillable = [
        'ged_document_id',
        'attachable_type',
        'attachable_id',
        'role',
        'created_by',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(GedDocument::class, 'ged_document_id');
    }

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
