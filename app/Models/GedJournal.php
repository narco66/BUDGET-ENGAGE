<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GedJournal extends Model
{
    protected $table = 'ged_journaux';

    public $timestamps = false;

    protected $fillable = [
        'ged_document_id',
        'user_id',
        'action',
        'module_source',
        'anciennes_valeurs',
        'nouvelles_valeurs',
        'commentaire',
        'adresse_ip',
        'user_agent',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'anciennes_valeurs' => 'array',
            'nouvelles_valeurs' => 'array',
            'created_at' => 'datetime',
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
