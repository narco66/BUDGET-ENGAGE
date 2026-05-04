<?php

namespace App\Models;

use Database\Factories\AgentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agent extends Model
{
    /** @use HasFactory<AgentFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'service_id',
        'matricule',
        'nom',
        'prenom',
        'fonction',
        'grade',
        'categorie',
        'email',
        'telephone',
        'banque',
        'compte_bancaire',
        'statut',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function salairesCharges(): HasMany
    {
        return $this->hasMany(SalaireChargePersonnel::class);
    }
}
