<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PieceJustificative extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'engagement_id',
        'type_piece',
        'nom_original',
        'nom_stocke',
        'chemin',
        'extension',
        'mime_type',
        'taille',
        'version',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'taille' => 'integer',
            'version' => 'integer',
        ];
    }

    public function engagement(): BelongsTo
    {
        return $this->belongsTo(Engagement::class);
    }

    public function deposant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
