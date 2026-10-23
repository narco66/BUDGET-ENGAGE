<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class RbmModificationHistorique extends Model
{
    protected $table = 'rbm_modification_historiques';

    protected $fillable = [
        'historisable_type',
        'historisable_id',
        'action',
        'anciennes_valeurs',
        'nouvelles_valeurs',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'anciennes_valeurs' => 'array',
            'nouvelles_valeurs' => 'array',
        ];
    }

    public function historisable(): MorphTo
    {
        return $this->morphTo();
    }

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
