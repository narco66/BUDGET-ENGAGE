<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LiquidationServiceFait extends Model
{
    use SoftDeletes;

    protected $table = 'liquidation_services_faits';

    protected $fillable = [
        'liquidation_id',
        'date_constatation',
        'responsable_id',
        'commission',
        'conformite_quantitative',
        'conformite_qualitative',
        'conformite_technique',
        'conformite_contractuelle',
        'reserves',
        'reserves_levees',
        'reserves_levees_at',
        'observations',
        'valide',
        'valide_par',
        'valide_at',
    ];

    protected function casts(): array
    {
        return [
            'date_constatation' => 'date',
            'conformite_quantitative' => 'boolean',
            'conformite_qualitative' => 'boolean',
            'conformite_technique' => 'boolean',
            'conformite_contractuelle' => 'boolean',
            'reserves_levees' => 'boolean',
            'reserves_levees_at' => 'datetime',
            'valide' => 'boolean',
            'valide_at' => 'datetime',
        ];
    }

    public function liquidation(): BelongsTo
    {
        return $this->belongsTo(Liquidation::class);
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    public function validateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'valide_par');
    }

    /** Toutes les conformités requises sont satisfaites et les réserves levées. */
    public function estConforme(): bool
    {
        $conformites = [
            $this->conformite_quantitative,
            $this->conformite_qualitative,
            $this->conformite_technique,
            $this->conformite_contractuelle,
        ];

        foreach ($conformites as $conformite) {
            if ($conformite === false) {
                return false;
            }
        }

        if ($this->reserves && ! $this->reserves_levees) {
            return false;
        }

        return true;
    }
}
