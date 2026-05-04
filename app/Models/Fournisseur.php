<?php

namespace App\Models;

use Database\Factories\FournisseurFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fournisseur extends Model
{
    /** @use HasFactory<FournisseurFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'raison_sociale',
        'sigle',
        'numero_fiscal',
        'adresse',
        'telephone',
        'email',
        'banque',
        'compte_bancaire',
        'type_fournisseur',
        'statut',
    ];
}
