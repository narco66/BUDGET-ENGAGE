<?php

namespace App\Services\Rbm;

use App\Models\Activite;
use App\Models\AxeStrategique;
use App\Models\CadreLogique;
use App\Models\ChaineResultat;
use App\Models\Indicateur;
use App\Models\Pilier;
use App\Models\Produit;
use App\Models\RbmModificationHistorique;
use App\Models\SousProduit;
use App\Models\SourceVerification;
use App\Models\Tache;
use App\Models\TypeIndicateur;
use App\Models\UniteMesure;
use App\Models\User;
use App\Support\BudgetJournal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RbmReferentialService
{
    /**
     * @param  class-string<Model>  $modelClass
     * @param  array<string, mixed>  $data
     */
    public function create(string $modelClass, array $data, User $user): Model
    {
        return DB::transaction(function () use ($modelClass, $data, $user) {
            $data['created_by'] = $user->id;
            $data['updated_by'] = $user->id;

            /** @var Model $model */
            $model = $modelClass::query()->create($data);

            $this->recordHistory($model, 'creation', null, $model->getAttributes(), $user);
            BudgetJournal::record($user, "Création RBM : {$modelClass}", $model, ['code' => $data['code'] ?? null]);

            return $model;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Model $model, array $data, User $user): Model
    {
        return DB::transaction(function () use ($model, $data, $user) {
            $anciennes = $model->getAttributes();
            $data['updated_by'] = $user->id;
            $model->update($data);

            $this->recordHistory($model, 'modification', $anciennes, $model->fresh()->getAttributes(), $user);
            BudgetJournal::record($user, 'Modification RBM', $model, ['code' => $model->code ?? null]);

            return $model->fresh();
        });
    }

    public function archive(Model $model, User $user): void
    {
        DB::transaction(function () use ($model, $user) {
            $this->assertCanArchive($model);

            $anciennes = $model->getAttributes();
            $model->delete();

            $this->recordHistory($model, 'archivage', $anciennes, null, $user);
            BudgetJournal::record($user, 'Archivage RBM', $model, ['code' => $model->code ?? null]);
        });
    }

    public function restore(Model $model, User $user): Model
    {
        return DB::transaction(function () use ($model, $user) {
            $model->restore();

            $this->recordHistory($model, 'restauration', null, $model->fresh()->getAttributes(), $user);
            BudgetJournal::record($user, 'Restauration RBM', $model, ['code' => $model->code ?? null]);

            return $model->fresh();
        });
    }

    public function assertCanArchive(Model $model): void
    {
        $usageGuard = app(RbmUsageGuardService::class);

        if ($usageGuard->isInUse($model)) {
            throw ValidationException::withMessages([
                'archive' => [__('Cet élément est utilisé et ne peut pas être archivé.')],
            ]);
        }

        if ($usageGuard->hasActiveChildren($model)) {
            throw ValidationException::withMessages([
                'archive' => [__('Cet élément possède des éléments enfants actifs.')],
            ]);
        }
    }

    /**
     * @param  array<string, mixed>|null  $anciennes
     * @param  array<string, mixed>|null  $nouvelles
     */
    protected function recordHistory(
        Model $model,
        string $action,
        ?array $anciennes,
        ?array $nouvelles,
        User $user
    ): void {
        RbmModificationHistorique::query()->create([
            'historisable_type' => $model->getMorphClass(),
            'historisable_id' => $model->getKey(),
            'action' => $action,
            'anciennes_valeurs' => $anciennes,
            'nouvelles_valeurs' => $nouvelles,
            'user_id' => $user->id,
        ]);
    }
}
