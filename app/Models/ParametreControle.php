<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParametreControle extends Model
{
    protected $table = 'parametres_controle';

    protected $fillable = [
        'cle',
        'valeur',
        'type_valeur',
        'description',
        'actif',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'actif' => 'boolean',
        ];
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
