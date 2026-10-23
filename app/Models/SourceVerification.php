<?php

namespace App\Models;

use App\Models\Concerns\HasRbmReferentialAttributes;
use Database\Factories\SourceVerificationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SourceVerification extends Model
{
    /** @use HasFactory<SourceVerificationFactory> */
    use HasFactory;
    use HasRbmReferentialAttributes;
    use SoftDeletes;

    protected $table = 'sources_verification';

    protected $fillable = [
        'code',
        'libelle',
        'description',
        'ordre',
        'statut',
        'created_by',
        'updated_by',
    ];

    public function indicateurs(): BelongsToMany
    {
        return $this->belongsToMany(Indicateur::class, 'indicateur_source_verification')->withTimestamps();
    }
}
