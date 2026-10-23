<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class GedTag extends Model
{
    use HasFactory;

    protected $table = 'ged_tags';

    protected $fillable = [
        'libelle',
        'slug',
        'couleur',
        'created_by',
    ];

    public function documents(): BelongsToMany
    {
        return $this->belongsToMany(GedDocument::class, 'ged_document_tags', 'ged_tag_id', 'ged_document_id')
            ->withTimestamps();
    }

    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
