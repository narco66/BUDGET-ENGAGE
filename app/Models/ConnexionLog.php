<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConnexionLog extends Model
{
    protected $table = 'connexion_logs';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'email',
        'succes',
        'adresse_ip',
        'user_agent',
        'connecte_le',
    ];

    protected function casts(): array
    {
        return [
            'succes' => 'boolean',
            'connecte_le' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
