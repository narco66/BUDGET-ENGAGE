<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GedCategorie extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'ged_categories';

    protected $fillable = [
        'code',
        'libelle',
        'description',
        'parent_id',
        'ordre',
        'couleur',
        'icone',
        'statut',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'ordre' => 'integer',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function enfants(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function typesDocuments(): HasMany
    {
        return $this->hasMany(GedTypeDocument::class, 'categorie_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(GedDocument::class, 'categorie_id');
    }

    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function modificateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
