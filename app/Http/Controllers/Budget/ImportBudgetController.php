<?php

namespace App\Http\Controllers\Budget;

use App\Enums\StatutImportBudget;
use App\Http\Controllers\Controller;
use App\Http\Requests\Budget\StoreImportBudgetRequest;
use App\Models\Departement;
use App\Models\Direction;
use App\Models\ImportBudget;
use App\Models\LigneBudgetaire;
use App\Models\NatureDepense;
use App\Models\Service;
use App\Models\SourceFinancement;
use App\Services\Budget\BudgetLegacyImportBridge;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ImportBudgetController extends Controller
{
    public function __construct(private readonly BudgetLegacyImportBridge $legacyBridge)
    {
    }

    /**
     * @deprecated Redirige vers le modèle nomenclature 2026.
     */
    public function downloadTemplate(): RedirectResponse
    {
        return redirect()
            ->route('budget.nomenclature.template')
            ->with('status', __('Le modèle CSV legacy est remplacé par le modèle nomenclature budget.'));
    }

    /**
     * @deprecated Utiliser budget.nomenclature.store
     */
    public function store(StoreImportBudgetRequest $request): RedirectResponse
    {
        $file = $request->file('fichier');
        $path = $file->store('imports-budget', 'private');
        $rows = Excel::toArray([], $file)[0] ?? [];
        [$normalizedRows, $errors] = $this->normalizeRows($rows);

        $import = ImportBudget::query()->create([
            'exercice_budgetaire_id' => $request->integer('exercice_budgetaire_id'),
            'fichier_original' => $file->getClientOriginalName(),
            'chemin_fichier' => $path,
            'statut' => $errors === [] ? StatutImportBudget::EnValidation : StatutImportBudget::Rejete,
            'total_lignes' => count($normalizedRows),
            'lignes_valides' => count($normalizedRows) - count($errors),
            'lignes_rejetees' => count($errors),
            'rapport_erreurs' => $errors === [] ? null : $errors,
            'imported_by' => $request->user()->id,
            'imported_at' => now(),
        ]);

        $status = $errors === []
            ? __('Import legacy chargé. Utilisez désormais le module nomenclature pour les prochains imports.')
            : __('Import chargé avec erreurs. Consultez le rapport.');

        $this->legacyBridge->migrate($import);

        return redirect()
            ->route('budget.nomenclature.index')
            ->with('status', $status);
    }

    /**
     * @deprecated Préférer budget.nomenclature.show
     */
    public function show(ImportBudget $import): JsonResponse|RedirectResponse
    {
        abort_unless(request()->user()->can('budget.import'), 403);

        $modern = $this->legacyBridge->findOrMigrate($import);

        if (! request()->expectsJson()) {
            return redirect()->route('budget.nomenclature.show', $modern)
                ->with('status', __('Import legacy migré vers le registre nomenclature.'));
        }

        return response()->json([
            'id' => $import->id,
            'statut' => $import->statut->value,
            'total_lignes' => $import->total_lignes,
            'lignes_valides' => $import->lignes_valides,
            'lignes_rejetees' => $import->lignes_rejetees,
            'rapport_erreurs' => $import->rapport_erreurs,
            'migrated_budget_import_id' => $modern->id,
            'nomenclature_url' => route('budget.nomenclature.show', $modern),
        ]);
    }

    public function validateImport(Request $request, ImportBudget $import): RedirectResponse
    {
        abort_unless($request->user()->can('budget.import'), 403);
        abort_if($import->statut === StatutImportBudget::Annule, 422);

        $absolutePath = Storage::disk('private')->path($import->chemin_fichier);
        $rows = Excel::toArray([], $absolutePath)[0] ?? [];
        [$normalizedRows, $errors] = $this->normalizeRows($rows);

        if ($errors !== []) {
            $import->update([
                'statut' => StatutImportBudget::Rejete,
                'rapport_erreurs' => $errors,
                'validated_by' => $request->user()->id,
                'validated_at' => now(),
            ]);

            return redirect()->route('budget.nomenclature.index')
                ->with('status', __('Validation impossible: erreurs de cohérence détectées.'));
        }

        DB::transaction(function () use ($import, $normalizedRows, $request): void {
            foreach ($normalizedRows as $row) {
                $departement = Departement::query()->firstOrCreate(
                    ['code' => $row['code_departement']],
                    ['libelle' => $row['departement'], 'statut' => 'actif']
                );

                $direction = Direction::query()->firstOrCreate(
                    ['departement_id' => $departement->id, 'code' => $row['code_direction']],
                    ['libelle' => $row['direction'], 'statut' => 'actif']
                );

                $service = null;
                if ($row['code_service'] !== null) {
                    $service = Service::query()->firstOrCreate(
                        ['direction_id' => $direction->id, 'code' => $row['code_service']],
                        ['libelle' => $row['service'] ?? $row['code_service'], 'statut' => 'actif']
                    );
                }

                $nature = NatureDepense::query()->firstOrCreate(
                    ['code' => mb_substr(md5($row['nature_depense']), 0, 10)],
                    ['libelle' => $row['nature_depense'], 'statut' => 'actif']
                );

                $source = null;
                if ($row['source_financement'] !== null) {
                    $source = SourceFinancement::query()->firstOrCreate(
                        ['code' => mb_substr(md5($row['source_financement']), 0, 10)],
                        ['libelle' => $row['source_financement'], 'statut' => 'actif']
                    );
                }

                LigneBudgetaire::query()->updateOrCreate(
                    [
                        'exercice_budgetaire_id' => $import->exercice_budgetaire_id,
                        'code' => $row['code_ligne_budgetaire'],
                        'service_id' => $service?->id,
                    ],
                    [
                        'departement_id' => $departement->id,
                        'direction_id' => $direction->id,
                        'nature_depense_id' => $nature->id,
                        'source_financement_id' => $source?->id,
                        'libelle' => $row['libelle_ligne_budgetaire'],
                        'montant_vote' => $row['montant_vote'],
                        'montant_revise' => $row['montant_revise'] ?? $row['montant_vote'],
                        'statut' => 'active',
                    ]
                );
            }

            $import->update([
                'statut' => StatutImportBudget::Valide,
                'validated_by' => $request->user()->id,
                'validated_at' => now(),
                'rapport_erreurs' => null,
            ]);
        });

        return redirect()->route('budget.nomenclature.index')
            ->with('status', __('Import validé et lignes budgétaires générées. Consultez la situation budgétaire pour les restes.'));
    }

    public function cancel(Request $request, ImportBudget $import): RedirectResponse
    {
        abort_unless($request->user()->can('budget.import'), 403);
        abort_if($import->statut === StatutImportBudget::Valide, 422);

        $import->update([
            'statut' => StatutImportBudget::Annule,
            'validated_by' => $request->user()->id,
            'validated_at' => now(),
        ]);

        if ($import->migrated_budget_import_id) {
            $import->budgetImport?->update(['statut' => 'annule']);
        }

        return redirect()->route('budget.nomenclature.index')
            ->with('status', __('Import annulé.'));
    }

    /**
     * @param  array<int, array<int, mixed>>  $rows
     * @return array{0: array<int, array<string, mixed>>, 1: array<int, array<string, mixed>>}
     */
    protected function normalizeRows(array $rows): array
    {
        if ($rows === []) {
            return [[], [['line' => 0, 'error' => 'Fichier vide']]];
        }

        $headers = array_map(fn ($v) => $this->normalizeHeader((string) $v), $rows[0]);
        $required = [
            'exercice',
            'code_departement',
            'departement',
            'code_direction',
            'direction',
            'code_ligne_budgetaire',
            'libelle_ligne_budgetaire',
            'nature_de_depense',
            'montant_vote',
        ];

        $missing = array_values(array_diff($required, $headers));
        if ($missing !== []) {
            return [[], [['line' => 1, 'error' => 'Colonnes manquantes: '.implode(', ', $missing)]]];
        }

        $normalized = [];
        $errors = [];
        foreach (array_slice($rows, 1) as $index => $row) {
            if ($row === [] || collect($row)->filter(fn ($v) => filled($v))->isEmpty()) {
                continue;
            }
            $assoc = [];
            foreach ($headers as $i => $key) {
                $assoc[$key] = $row[$i] ?? null;
            }

            $montantVote = (float) ($assoc['montant_vote'] ?? 0);
            if ($montantVote < 0) {
                $errors[] = ['line' => $index + 2, 'error' => 'Montant vote negatif'];

                continue;
            }

            if ((string) ($assoc['code_ligne_budgetaire'] ?? '') === '') {
                $errors[] = ['line' => $index + 2, 'error' => 'Code ligne budgetaire obligatoire'];

                continue;
            }

            $normalized[] = [
                'exercice' => (int) $assoc['exercice'],
                'code_departement' => (string) $assoc['code_departement'],
                'departement' => (string) $assoc['departement'],
                'code_direction' => (string) $assoc['code_direction'],
                'direction' => (string) $assoc['direction'],
                'code_service' => filled($assoc['code_service'] ?? null) ? (string) $assoc['code_service'] : null,
                'service' => filled($assoc['service'] ?? null) ? (string) $assoc['service'] : null,
                'code_ligne_budgetaire' => (string) $assoc['code_ligne_budgetaire'],
                'libelle_ligne_budgetaire' => (string) $assoc['libelle_ligne_budgetaire'],
                'nature_depense' => (string) $assoc['nature_de_depense'],
                'source_financement' => filled($assoc['source_de_financement'] ?? null) ? (string) $assoc['source_de_financement'] : null,
                'montant_vote' => $montantVote,
                'montant_revise' => filled($assoc['montant_revise'] ?? null) ? (float) $assoc['montant_revise'] : null,
            ];
        }

        return [$normalized, $errors];
    }

    protected function normalizeHeader(string $header): string
    {
        $normalized = trim(mb_strtolower($header));
        $normalized = str_replace([' ', '-', "'", '’'], '_', $normalized);
        $normalized = preg_replace('/[^a-z0-9_]/', '', $normalized) ?? $normalized;
        $normalized = preg_replace('/_+/', '_', $normalized) ?? $normalized;

        return $normalized;
    }
}
