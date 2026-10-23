<?php

namespace App\Http\Controllers\Engagements;

use App\Enums\DecisionWorkflow;
use App\Enums\StatutEngagement;
use App\Http\Controllers\Controller;
use App\Http\Requests\Engagements\CancelApprovedEngagementRequest;
use App\Http\Requests\Engagements\StoreEngagementRequest;
use App\Http\Requests\Engagements\UpdateEngagementRequest;
use App\Http\Requests\Engagements\WorkflowDecisionRequest;
use App\Models\Activite;
use App\Models\Agent;
use App\Models\Departement;
use App\Models\Direction;
use App\Models\Engagement;
use App\Models\EngagementImputation;
use App\Models\EngagementType;
use App\Models\ExerciceBudgetaire;
use App\Models\Fournisseur;
use App\Models\LigneBudgetaire;
use App\Models\Service;
use App\Models\SourceFinancement;
use App\Policies\EngagementPolicy;
use App\Services\Budget\CreditAvailabilityService;
use App\Services\Engagement\EngagementAuditService;
use App\Services\Engagement\EngagementTypeService;
use App\Services\Engagement\EngagementVersionService;
use App\Services\EngagementCancellationService;
use App\Services\EngagementWorkflowService;
use App\Support\BudgetJournal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EngagementController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Engagement::class, 'engagement');
    }

    public function index(Request $request): View
    {
        $query = Engagement::query()
            ->with([
                'exerciceBudgetaire',
                'ligneBudgetaire',
                'departement',
                'createur',
            ])
            ->orderByDesc('created_at');

        if ($request->filled('q')) {
            $q = trim((string) $request->input('q'));
            $query->where(function ($builder) use ($q): void {
                $builder->where('numero', 'like', "%{$q}%")
                    ->orWhere('objet', 'like', "%{$q}%");
            });
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->string('statut')->value());
        }

        if ($request->filled('exercice_budgetaire_id')) {
            $query->where('exercice_budgetaire_id', $request->integer('exercice_budgetaire_id'));
        }

        if (! EngagementPolicy::seesAllEngagements($request->user())) {
            $query->where('created_by', $request->user()->id);
        }

        $engagements = $query->paginate(20);
        $engagements->appends($request->query());

        $exercices = ExerciceBudgetaire::query()->orderByDesc('annee')->get();

        return view('engagements.index', compact('engagements', 'exercices'));
    }

    public function create(): View
    {
        return view('engagements.create', $this->formDependencies());
    }

    public function store(StoreEngagementRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $imputations = $this->normalizeImputations($validated['imputations'] ?? []);
        $this->assertImputationsWithinCredit($imputations, null);

        $exercice = ExerciceBudgetaire::query()->findOrFail((int) $validated['exercice_budgetaire_id']);
        $total = $this->computeImputationsTotal($imputations);

        $typeValeurs = (array) ($validated['type_valeurs'] ?? []);
        unset($validated['type_valeurs']);
        $validated['mode_creation'] = $validated['mode_creation'] ?? 'manuel';
        $validated['numero'] = $this->generateUniqueNumero($exercice->annee);
        $validated['statut'] = StatutEngagement::Brouillon;
        $validated['created_by'] = $request->user()->id;
        $validated['montant'] = $total;
        $validated['ligne_budgetaire_id'] = $imputations[0]['ligne_budgetaire_id'];
        unset($validated['imputations']);

        DB::transaction(function () use ($validated, $imputations, $typeValeurs, $request): void {
            $engagement = Engagement::query()->create($validated);

            foreach ($imputations as $imputation) {
                $created = $engagement->imputations()->create($imputation);
                BudgetJournal::record($request->user(), 'Création imputation budgétaire.', $engagement, [
                    'engagement_id' => $engagement->id,
                    'imputation_id' => $created->id,
                    'ligne_budgetaire_id' => $created->ligne_budgetaire_id,
                    'montant_impute' => (string) $created->montant_impute,
                ]);
            }

            app(EngagementTypeService::class)->syncTypeValeurs($engagement, $engagement->engagement_type_id, $typeValeurs);
            app(EngagementAuditService::class)->record($engagement, $request->user(), 'creation', 'Création de l’engagement en brouillon.', [
                'mode_creation' => $engagement->mode_creation,
            ]);

            BudgetJournal::record($request->user(), 'Recalcul total engagement depuis imputations.', $engagement, [
                'engagement_id' => $engagement->id,
                'montant_total' => (string) $engagement->montant,
                'imputations_count' => count($imputations),
            ]);
        });

        return redirect()->route('engagements.index')->with('status', __('Engagement enregistré en brouillon.'));
    }

    public function show(Engagement $engagement): View
    {
        $engagement->load([
            'exerciceBudgetaire',
            'engagementType',
            'typeValeurs',
            'ligneBudgetaire.natureDepense',
            'imputations.ligneBudgetaire',
            'imputations.sourceFinancement',
            'imputations.activite',
            'departement',
            'direction',
            'serviceStructure',
            'createur',
            'reservationsBudgetaires.ligneBudgetaire',
            'versions.createur',
            'piecesJustificatives.deposant',
            'validationsWorkflow.validateur',
            'liquidations.createur',
            'liquidations.engagementImputation.ligneBudgetaire',
            'liquidations.ordonnancements.createur',
            'liquidations.ordonnancements.engagementImputation.ligneBudgetaire',
            'liquidations.ordonnancements.paiements.createur',
            'liquidations.ordonnancements.paiements.engagementImputation.ligneBudgetaire',
            'expressionBesoin',
        ]);

        $rowsConsolide = $engagement->imputations->isNotEmpty()
            ? $engagement->imputations
            : collect([(object) ['id' => null, 'ligneBudgetaire' => $engagement->ligneBudgetaire, 'montant_impute' => $engagement->montant]]);

        $gedDocuments = $engagement->gedDocuments()->get();
        $timeline = app(EngagementAuditService::class)->timeline($engagement);

        return view('engagements.show', compact('engagement', 'rowsConsolide', 'gedDocuments', 'timeline'));
    }

    public function workflow(Engagement $engagement): View
    {
        $this->authorize('view', $engagement);

        $engagement->load([
            'validationsWorkflow.validateur',
            'imputations.ligneBudgetaire',
            'ligneBudgetaire',
            'createur',
        ]);

        return view('engagements.workflow', compact('engagement'));
    }

    public function edit(Engagement $engagement): View
    {
        $engagement->load(['imputations', 'typeValeurs']);

        return view('engagements.edit', array_merge(
            ['engagement' => $engagement],
            $this->formDependencies()
        ));
    }

    public function update(UpdateEngagementRequest $request, Engagement $engagement): RedirectResponse
    {
        $validated = $request->validated();
        $imputations = $this->normalizeImputations($validated['imputations'] ?? []);
        $this->assertImputationsWithinCredit($imputations, $engagement);
        $total = $this->computeImputationsTotal($imputations);
        $typeValeurs = (array) ($validated['type_valeurs'] ?? []);
        // mode_creation n'est jamais modifié lors d'une édition.
        unset($validated['type_valeurs'], $validated['mode_creation']);
        $validated['montant'] = $total;
        $validated['ligne_budgetaire_id'] = $imputations[0]['ligne_budgetaire_id'];
        unset($validated['imputations']);

        // Historise l'état courant (anciennes valeurs) avant application des modifications.
        app(EngagementVersionService::class)->snapshot($engagement, $request->user(), 'Modification de l’engagement.');

        DB::transaction(function () use ($request, $engagement, $validated, $imputations, $typeValeurs, $total): void {
            $engagement->update($validated);
            app(EngagementTypeService::class)->syncTypeValeurs($engagement, $engagement->engagement_type_id, $typeValeurs);
            app(EngagementAuditService::class)->record($engagement, $request->user(), 'modification', 'Modification de l’engagement (nouvelle version).');

            $existing = $engagement->imputations()->get()->keyBy('id');
            $kept = [];

            foreach ($imputations as $row) {
                $rowId = $row['id'] ?? null;
                unset($row['id']);

                if ($rowId !== null && $existing->has($rowId)) {
                    /** @var EngagementImputation $current */
                    $current = $existing->get($rowId);
                    $changed = $current->ligne_budgetaire_id !== $row['ligne_budgetaire_id']
                        || bccomp((string) $current->montant_impute, (string) $row['montant_impute'], 2) !== 0
                        || (string) $current->observation !== (string) ($row['observation'] ?? '');

                    $current->update($row);
                    $kept[] = $current->id;

                    if ($changed) {
                        BudgetJournal::record($request->user(), 'Modification imputation budgétaire.', $engagement, [
                            'engagement_id' => $engagement->id,
                            'imputation_id' => $current->id,
                            'ligne_budgetaire_id' => $current->ligne_budgetaire_id,
                            'montant_impute' => (string) $current->montant_impute,
                        ]);
                    }

                    continue;
                }

                $created = $engagement->imputations()->create($row);
                $kept[] = $created->id;
                BudgetJournal::record($request->user(), 'Création imputation budgétaire.', $engagement, [
                    'engagement_id' => $engagement->id,
                    'imputation_id' => $created->id,
                    'ligne_budgetaire_id' => $created->ligne_budgetaire_id,
                    'montant_impute' => (string) $created->montant_impute,
                ]);
            }

            $deleted = $existing->keys()->diff($kept);
            if ($deleted->isNotEmpty()) {
                foreach ($existing->only($deleted->all()) as $removed) {
                    BudgetJournal::record($request->user(), 'Suppression imputation budgétaire.', $engagement, [
                        'engagement_id' => $engagement->id,
                        'imputation_id' => $removed->id,
                        'ligne_budgetaire_id' => $removed->ligne_budgetaire_id,
                        'montant_impute' => (string) $removed->montant_impute,
                    ]);
                }
                $engagement->imputations()->whereIn('id', $deleted->all())->delete();
            }

            BudgetJournal::record($request->user(), 'Recalcul total engagement depuis imputations.', $engagement, [
                'engagement_id' => $engagement->id,
                'montant_total' => $total,
                'imputations_count' => count($imputations),
            ]);
        });

        return redirect()->route('engagements.index')->with('status', __('Engagement mis à jour.'));
    }

    public function destroy(Engagement $engagement): RedirectResponse
    {
        $engagement->delete();

        return redirect()->route('engagements.index')->with('status', __('Engagement supprimé.'));
    }

    public function submit(Request $request, Engagement $engagement): RedirectResponse
    {
        $this->authorize('submit', $engagement);

        if ($engagement->is_urgence && blank($engagement->justification_urgence)) {
            throw ValidationException::withMessages([
                'justification_urgence' => __('Une justification est requise pour une procédure d’urgence.'),
            ]);
        }

        // Controle de disponibilite previsionnelle des credits avant entree en circuit de validation.
        app(CreditAvailabilityService::class)->assertEngagement($engagement);

        app(EngagementWorkflowService::class)->submitForValidation($engagement, $request->user());

        return redirect()->route('engagements.show', $engagement->fresh())->with('status', __('Engagement transmis en circuit de validation.'));
    }

    public function workflowDecision(WorkflowDecisionRequest $request, Engagement $engagement): RedirectResponse
    {
        $decision = DecisionWorkflow::from($request->validated('decision'));

        app(EngagementWorkflowService::class)->processDecision(
            $engagement,
            $request->user(),
            $decision,
            $request->validated('commentaire')
        );

        $engagement->refresh();

        $statusMessage = match ($decision) {
            DecisionWorkflow::Valide => $engagement->statut === StatutEngagement::Approuve
                ? __('workflow.messages.approved')
                : __('workflow.messages.validated'),
            DecisionWorkflow::Rejete => __('workflow.messages.rejected'),
            DecisionWorkflow::Retourne => __('workflow.messages.returned'),
            default => __('Décision enregistrée.'),
        };

        return redirect()->route('engagements.show', $engagement)->with('status', $statusMessage);
    }

    public function cancelApproved(CancelApprovedEngagementRequest $request, Engagement $engagement): RedirectResponse
    {
        app(EngagementCancellationService::class)->cancelApproved(
            $engagement,
            $request->validated('motif_annulation'),
            $request->user()
        );

        return redirect()
            ->route('engagements.show', $engagement->fresh())
            ->with('status', __('execution.messages.engagement_cancelled'));
    }

    public function cloturer(Request $request, Engagement $engagement): RedirectResponse
    {
        $this->authorize('cloturer', $engagement);

        app(EngagementWorkflowService::class)->cloturer($engagement, $request->user());

        return redirect()
            ->route('engagements.show', $engagement->fresh())
            ->with('status', __('Engagement clôturé.'));
    }

    /**
     * @param  array<int, array{id?: int, ligne_budgetaire_id: int, montant_impute: string, observation?: string|null}>  $imputations
     */
    protected function assertImputationsWithinCredit(array $imputations, ?Engagement $current): void
    {
        // Controle de disponibilite previsionnelle (plafond - engage - reservations en cours),
        // en excluant l'engagement courant de ses propres reservations lors d'une mise a jour.
        app(CreditAvailabilityService::class)
            ->assertImputations($imputations, $current?->id);
    }

    /**
     * @param  array<int, mixed>  $rows
     * @return array<int, array{id?: int, ligne_budgetaire_id: int, montant_impute: string, observation?: string|null}>
     */
    protected function normalizeImputations(array $rows): array
    {
        return collect($rows)
            ->map(function (array $row): array {
                $normalized = [
                    'ligne_budgetaire_id' => (int) $row['ligne_budgetaire_id'],
                    'source_financement_id' => blank($row['source_financement_id'] ?? null) ? null : (int) $row['source_financement_id'],
                    'activite_id' => blank($row['activite_id'] ?? null) ? null : (int) $row['activite_id'],
                    'centre_cout' => blank($row['centre_cout'] ?? null) ? null : (string) $row['centre_cout'],
                    'centre_responsabilite' => blank($row['centre_responsabilite'] ?? null) ? null : (string) $row['centre_responsabilite'],
                    'montant_impute' => number_format((float) $row['montant_impute'], 2, '.', ''),
                    'observation' => blank($row['observation'] ?? null) ? null : (string) $row['observation'],
                ];

                if (isset($row['id'])) {
                    $normalized['id'] = (int) $row['id'];
                }

                return $normalized;
            })
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array{montant_impute: string}>  $imputations
     */
    protected function computeImputationsTotal(array $imputations): string
    {
        $total = '0';
        foreach ($imputations as $imputation) {
            $total = bcadd($total, $imputation['montant_impute'], 2);
        }

        return $total;
    }

    protected function generateUniqueNumero(int $annee): string
    {
        return DB::transaction(function () use ($annee): string {
            do {
                $numero = sprintf('ENG-%d-%s', $annee, strtoupper(Str::random(8)));
            } while (Engagement::withTrashed()->where('numero', $numero)->exists());

            return $numero;
        });
    }

    /**
     * @return array<string, mixed>
     */
    protected function formDependencies(): array
    {
        return [
            'exercices' => ExerciceBudgetaire::query()->orderByDesc('annee')->get(),
            'lignes' => $lignesForm = LigneBudgetaire::query()
                ->with('exerciceBudgetaire')
                ->orderByDesc('exercice_budgetaire_id')
                ->orderBy('code')
                ->get(),
            'disponibles' => app(CreditAvailabilityService::class)->disponibleMap($lignesForm),
            'departements' => Departement::query()->orderBy('libelle')->get(),
            'directions' => Direction::query()->with('departement')->orderBy('libelle')->get(),
            'services' => Service::query()->with('direction.departement')->orderBy('libelle')->get(),
            'fournisseurs' => Fournisseur::query()->orderBy('raison_sociale')->get(),
            'agents' => Agent::query()->orderBy('nom')->orderBy('prenom')->get(),
            'engagementTypes' => EngagementType::query()->where('est_actif', true)->orderBy('ordre')->orderBy('libelle')->get(),
            'sourcesFinancement' => SourceFinancement::query()->orderBy('libelle')->get(['id', 'code', 'libelle']),
            'activites' => Activite::query()->orderBy('code')->get(['id', 'code', 'libelle']),
        ];
    }
}
