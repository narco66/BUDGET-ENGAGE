<?php

namespace App\Services\Liquidation;

use App\Models\Liquidation;
use App\Models\LiquidationReception;
use App\Models\LiquidationServiceFait;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Moteur de contrôle du service fait : constatation, conformités
 * (quantitative / qualitative / technique / contractuelle), réserves et leur
 * levée, réceptions provisoires/définitives. Empêche toute liquidation sans
 * service fait validé.
 */
class LiquidationServiceFaitService
{
    public function __construct(private readonly LiquidationAuditService $audit)
    {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function enregistrer(Liquidation $liquidation, array $data, User $user): LiquidationServiceFait
    {
        return DB::transaction(function () use ($liquidation, $data, $user): LiquidationServiceFait {
            $serviceFait = $liquidation->servicesFaits()->create([
                'date_constatation' => $data['date_constatation'],
                'responsable_id' => $data['responsable_id'] ?? $user->id,
                'commission' => $data['commission'] ?? null,
                'conformite_quantitative' => $data['conformite_quantitative'] ?? null,
                'conformite_qualitative' => $data['conformite_qualitative'] ?? null,
                'conformite_technique' => $data['conformite_technique'] ?? null,
                'conformite_contractuelle' => $data['conformite_contractuelle'] ?? null,
                'reserves' => $data['reserves'] ?? null,
                'observations' => $data['observations'] ?? null,
            ]);

            $this->audit->record($liquidation, $user, 'service_fait_constate', 'Constatation du service fait.', [
                'service_fait_id' => $serviceFait->id,
            ]);

            return $serviceFait;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function enregistrerReception(Liquidation $liquidation, array $data, User $user): LiquidationReception
    {
        return DB::transaction(function () use ($liquidation, $data, $user): LiquidationReception {
            $reception = $liquidation->receptions()->create([
                'type_reception' => $data['type_reception'],
                'date_reception' => $data['date_reception'],
                'numero_pv' => $data['numero_pv'] ?? null,
                'commission' => $data['commission'] ?? null,
                'responsable_id' => $data['responsable_id'] ?? $user->id,
                'conforme' => $data['conforme'] ?? null,
                'reserves' => $data['reserves'] ?? null,
                'observations' => $data['observations'] ?? null,
            ]);

            $this->audit->record($liquidation, $user, 'reception_enregistree', 'Enregistrement d’une réception.', [
                'reception_id' => $reception->id,
                'type' => (string) $reception->type_reception?->value,
            ]);

            return $reception;
        });
    }

    public function leverReserves(LiquidationServiceFait $serviceFait, User $user, ?string $observations = null): LiquidationServiceFait
    {
        $serviceFait->update([
            'reserves_levees' => true,
            'reserves_levees_at' => now(),
            'observations' => $observations ?? $serviceFait->observations,
        ]);

        $this->audit->record($serviceFait->liquidation, $user, 'reserves_levees', 'Levée des réserves du service fait.', [
            'service_fait_id' => $serviceFait->id,
        ]);

        return $serviceFait;
    }

    /**
     * Valide le service fait après vérification des conformités et des réserves.
     */
    public function valider(LiquidationServiceFait $serviceFait, User $user): LiquidationServiceFait
    {
        if (! $serviceFait->estConforme()) {
            throw ValidationException::withMessages([
                'service_fait' => __('Le service fait comporte une non-conformité ou des réserves non levées.'),
            ]);
        }

        return DB::transaction(function () use ($serviceFait, $user): LiquidationServiceFait {
            $serviceFait->update([
                'valide' => true,
                'valide_par' => $user->id,
                'valide_at' => now(),
            ]);

            $serviceFait->liquidation()->update(['service_fait_valide' => true]);

            $this->audit->record($serviceFait->liquidation, $user, 'service_fait_valide', 'Validation du service fait.', [
                'service_fait_id' => $serviceFait->id,
            ]);

            return $serviceFait;
        });
    }

    /**
     * Garde-fou : interdit toute progression sans service fait validé lorsque le
     * type de liquidation l'exige.
     */
    public function assertServiceFaitValide(Liquidation $liquidation): void
    {
        $liquidation->loadMissing('type');

        if ($liquidation->type && $liquidation->type->exige_service_fait === false) {
            return;
        }

        if (! $liquidation->service_fait_valide) {
            throw ValidationException::withMessages([
                'service_fait' => __('La liquidation ne peut progresser sans service fait validé.'),
            ]);
        }
    }
}
