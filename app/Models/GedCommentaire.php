<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GedCommentaire extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'ged_commentaires';

    protected $fillable = [
        'ged_document_id',
        'user_id',
        'parent_id',
        'contenu',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(GedDocument::class, 'ged_document_id');
    }

    public function auteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function reponses(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}
