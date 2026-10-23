<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class RbmObservation extends Model
{
    protected $table = 'rbm_observations';

    protected $fillable = [
        'observable_type',
        'observable_id',
        'contenu',
        'user_id',
    ];

    public function observable(): MorphTo
    {
        return $this->morphTo();
    }

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
