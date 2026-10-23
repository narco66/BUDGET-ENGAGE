<?php

namespace App\Services\Rbm;

use App\Models\Activite;
use App\Models\AxeStrategique;
use App\Models\Indicateur;
use App\Models\Pilier;
use App\Models\Produit;
use App\Models\SousProduit;
use App\Models\Tache;
use App\Models\TypeIndicateur;
use App\Models\UniteMesure;
use App\Models\User;
use App\Support\BudgetJournal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RbmImportService
{
    /**
     * @param  array<int, array<int, mixed>>  $rawRows
     * @return array{rows: array<int, array<string, mixed>>, errors: array<int, string>}
     */
    public function parseAndValidate(array $rawRows): array
    {
        if ($rawRows === []) {
            return ['rows' => [], 'errors' => [__('Fichier vide.')]];
        }

        $headers = $this->normalizeHeaders(array_shift($rawRows));
        $rows = [];
        $errors = [];

        foreach ($rawRows as $index => $rawRow) {
            $line = $index + 2;
            $row = $this->mapRow($headers, $rawRow);

            if ($this->isEmptyRow($row)) {
                continue;
            }

            $rowErrors = $this->validateRow($row, $line);
            if ($rowErrors !== []) {
                $errors = array_merge($errors, $rowErrors);

                continue;
            }

            $rows[] = $row;
        }

        if ($rows === [] && $errors === []) {
            $errors[] = __('Aucune ligne de données exploitable.');
        }

        return ['rows' => $rows, 'errors' => $errors];
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array{imported: int, errors: array<int, string>}
     */
    public function import(array $rows, User $user): array
    {
        $imported = 0;

        DB::transaction(function () use ($rows, $user, &$imported): void {
            foreach ($rows as $row) {
                $this->importRow($row, $user);
                $imported++;
            }
        });

        BudgetJournal::record($user, 'Import RBM/GAR', null, [
            'lignes_importees' => $imported,
        ]);

        return ['imported' => $imported, 'errors' => []];
    }

    /**
     * @param  array<string, mixed>  $row
     */
    protected function importRow(array $row, User $user): void
    {
        $pilier = $this->upsertReferential(Pilier::class, ['code' => $row['pilier_code']], [
            'libelle' => $row['pilier_libelle'],
            'ordre' => $row['ordre'],
            'statut' => $row['statut'],
        ], $user);

        $axe = $this->upsertReferential(AxeStrategique::class, ['pilier_id' => $pilier->id, 'code' => $row['axe_code']], [
            'libelle' => $row['axe_libelle'],
            'ordre' => $row['ordre'],
            'statut' => $row['statut'],
        ], $user);

        $produit = $this->upsertReferential(Produit::class, ['axe_strategique_id' => $axe->id, 'code' => $row['produit_code']], [
            'libelle' => $row['produit_libelle'],
            'ordre' => $row['ordre'],
            'statut' => $row['statut'],
        ], $user);

        $sousProduit = $this->upsertReferential(SousProduit::class, ['produit_id' => $produit->id, 'code' => $row['sous_produit_code']], [
            'libelle' => $row['sous_produit_libelle'],
            'ordre' => $row['ordre'],
            'statut' => $row['statut'],
        ], $user);

        $activite = $this->upsertReferential(Activite::class, ['sous_produit_id' => $sousProduit->id, 'code' => $row['activite_code']], [
            'libelle' => $row['activite_libelle'],
            'ordre' => $row['ordre'],
            'statut' => $row['statut'],
        ], $user);

        $tache = $this->upsertReferential(Tache::class, ['activite_id' => $activite->id, 'code' => $row['tache_code']], [
            'libelle' => $row['tache_libelle'],
            'ordre' => $row['ordre'],
            'statut' => $row['statut'],
        ], $user);

        if ($row['indicateur_code'] === '') {
            return;
        }

        $type = TypeIndicateur::query()->where('code', $row['type_indicateur_code'])->firstOrFail();
        $unite = UniteMesure::query()->where('code', $row['unite_mesure_code'])->firstOrFail();

        $indicateur = $this->upsertReferential(Indicateur::class, ['code' => $row['indicateur_code']], [
            'libelle' => $row['indicateur_libelle'],
            'type_indicateur_id' => $type->id,
            'unite_mesure_id' => $unite->id,
            'ordre' => $row['ordre'],
            'statut' => $row['statut'],
        ], $user);

        $indicateur->taches()->syncWithoutDetaching([$tache->id]);
    }

    /**
     * @param  array<int, mixed>  $headers
     * @param  array<int, mixed>  $rawRow
     * @return array<string, mixed>
     */
    protected function mapRow(array $headers, array $rawRow): array
    {
        $mapped = [];
        foreach ($headers as $index => $key) {
            $mapped[$key] = trim((string) ($rawRow[$index] ?? ''));
        }

        return [
            'pilier_code' => $mapped['pilier_code'] ?? '',
            'pilier_libelle' => $mapped['pilier_libelle'] ?? '',
            'axe_code' => $mapped['axe_code'] ?? '',
            'axe_libelle' => $mapped['axe_libelle'] ?? '',
            'produit_code' => $mapped['produit_code'] ?? '',
            'produit_libelle' => $mapped['produit_libelle'] ?? '',
            'sous_produit_code' => $mapped['sous_produit_code'] ?? '',
            'sous_produit_libelle' => $mapped['sous_produit_libelle'] ?? '',
            'activite_code' => $mapped['activite_code'] ?? '',
            'activite_libelle' => $mapped['activite_libelle'] ?? '',
            'tache_code' => $mapped['tache_code'] ?? '',
            'tache_libelle' => $mapped['tache_libelle'] ?? '',
            'indicateur_code' => $mapped['indicateur_code'] ?? '',
            'indicateur_libelle' => $mapped['indicateur_libelle'] ?? '',
            'type_indicateur_code' => $mapped['type_indicateur_code'] ?? '',
            'unite_mesure_code' => $mapped['unite_mesure_code'] ?? '',
            'ordre' => is_numeric($mapped['ordre'] ?? '') ? (int) $mapped['ordre'] : 0,
            'statut' => in_array($mapped['statut'] ?? '', ['actif', 'inactif'], true) ? $mapped['statut'] : 'actif',
        ];
    }

    /**
     * @param  array<int, mixed>  $headerRow
     * @return array<int, string>
     */
    protected function normalizeHeaders(array $headerRow): array
    {
        $aliases = [
            'code pilier' => 'pilier_code',
            'libelle pilier' => 'pilier_libelle',
            'code axe' => 'axe_code',
            'libelle axe' => 'axe_libelle',
            'code produit' => 'produit_code',
            'libelle produit' => 'produit_libelle',
            'code sous-produit' => 'sous_produit_code',
            'code sous produit' => 'sous_produit_code',
            'libelle sous-produit' => 'sous_produit_libelle',
            'libelle sous produit' => 'sous_produit_libelle',
            'code activite' => 'activite_code',
            'libelle activite' => 'activite_libelle',
            'code tache' => 'tache_code',
            'libelle tache' => 'tache_libelle',
            'code indicateur' => 'indicateur_code',
            'libelle indicateur' => 'indicateur_libelle',
            'code type indicateur' => 'type_indicateur_code',
            'code unite mesure' => 'unite_mesure_code',
            'ordre' => 'ordre',
            'statut' => 'statut',
        ];

        $headers = [];
        foreach ($headerRow as $header) {
            $normalized = Str::ascii(mb_strtolower(trim((string) $header)));
            $headers[] = $aliases[$normalized] ?? Str::snake($normalized);
        }

        return $headers;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<int, string>
     */
    protected function validateRow(array $row, int $line): array
    {
        $errors = [];
        $required = [
            'pilier_code' => 'pilier',
            'pilier_libelle' => 'pilier',
            'axe_code' => 'axe',
            'axe_libelle' => 'axe',
            'produit_code' => 'produit',
            'produit_libelle' => 'produit',
            'sous_produit_code' => 'sous-produit',
            'sous_produit_libelle' => 'sous-produit',
            'activite_code' => 'activité',
            'activite_libelle' => 'activité',
            'tache_code' => 'tâche',
            'tache_libelle' => 'tâche',
        ];

        foreach ($required as $field => $label) {
            if ($row[$field] === '') {
                $errors[] = __('Ligne :line : le champ :field est obligatoire.', [
                    'line' => $line,
                    'field' => $label,
                ]);
            }
        }

        if ($row['indicateur_code'] !== '') {
            if ($row['indicateur_libelle'] === '') {
                $errors[] = __('Ligne :line : libellé indicateur obligatoire.', ['line' => $line]);
            }
            if ($row['type_indicateur_code'] === '') {
                $errors[] = __('Ligne :line : type indicateur obligatoire.', ['line' => $line]);
            } elseif (! TypeIndicateur::query()->where('code', $row['type_indicateur_code'])->exists()) {
                $errors[] = __('Ligne :line : type indicateur inconnu (:code).', [
                    'line' => $line,
                    'code' => $row['type_indicateur_code'],
                ]);
            }
            if ($row['unite_mesure_code'] === '') {
                $errors[] = __('Ligne :line : unité de mesure obligatoire.', ['line' => $line]);
            } elseif (! UniteMesure::query()->where('code', $row['unite_mesure_code'])->exists()) {
                $errors[] = __('Ligne :line : unité de mesure inconnue (:code).', [
                    'line' => $line,
                    'code' => $row['unite_mesure_code'],
                ]);
            }
        }

        return $errors;
    }

    /**
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $modelClass
     * @param  array<string, mixed>  $keys
     * @param  array<string, mixed>  $values
     */
    protected function upsertReferential(string $modelClass, array $keys, array $values, User $user): \Illuminate\Database\Eloquent\Model
    {
        $model = $modelClass::query()->updateOrCreate($keys, array_merge($values, [
            'updated_by' => $user->id,
        ]));

        if ($model->wasRecentlyCreated) {
            $model->update(['created_by' => $user->id]);
        }

        return $model;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    protected function isEmptyRow(array $row): bool
    {
        return $row['pilier_code'] === '' && $row['axe_code'] === '';
    }
}
