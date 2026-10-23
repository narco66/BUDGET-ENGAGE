<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GedAcces extends Model
{
    use HasFactory;

    protected $table = 'ged_acces';

    protected $fillable = [
        'ged_document_id',
        'user_id',
        'role_name',
        'peut_voir',
        'peut_telecharger',
        'peut_modifier',
        'peut_signer',
        'peut_partager',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'peut_voir' => 'boolean',
            'peut_telecharger' => 'boolean',
            'peut_modifier' => 'boolean',
            'peut_signer' => 'boolean',
            'peut_partager' => 'boolean',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(GedDocument::class, 'ged_document_id');
    }

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
