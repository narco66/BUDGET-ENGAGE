<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrdonnancementVersion extends Model
{
    protected $table = 'ordonnancement_versions';

    protected $fillable = [
        'ordonnancement_id',
        'numero_version',
        'snapshot',
        'motif',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'numero_version' => 'integer',
            'snapshot' => 'array',
        ];
    }

    public function ordonnancement(): BelongsTo
    {
        return $this->belongsTo(Ordonnancement::class);
    }

    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
