<?php

namespace App\Http\Controllers\ExpressionBesoins;

use App\Enums\NiveauUrgenceBesoin;
use App\Enums\PrioriteBesoin;
use App\Enums\StatutBesoin;
use App\Enums\TypeBesoin;
use App\Http\Controllers\Controller;
use App\Http\Requests\ExpressionBesoins\StoreExpressionBesoinRequest;
use App\Http\Requests\ExpressionBesoins\UpdateExpressionBesoinRequest;
use App\Models\Activite;
use App\Models\AxeStrategique;
use App\Models\Departement;
use App\Models\Direction;
use App\Models\ExerciceBudgetaire;
use App\Models\ExpressionBesoin;
use App\Models\Indicateur;
use App\Models\LigneBudgetaire;
use App\Models\Pilier;
use App\Models\Produit;
use App\Models\Service;
use App\Models\SourceFinancement;
use App\Models\SousProduit;
use App\Models\Tache;
use App\Models\User;
use App\Policies\ExpressionBesoinPolicy;
use App\Services\ExpressionBesoin\ExpressionBesoinService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpressionBesoinController extends Controller
{
    public function __construct(private readonly ExpressionBesoinService $service)
    {
        $this->authorizeResource(ExpressionBesoin::class, 'expression_besoin');
    }

    public function index(Request $request): View
    {
        $query = ExpressionBesoin::query()
            ->with(['exerciceBudgetaire', 'departement', 'createur'])
            ->orderByDesc('created_at');

        if ($request->filled('q')) {
            $q = trim((string) $request->input('q'));
            $query->where(fn ($b) => $b->where('reference', 'like', "%{$q}%")->orWhere('objet', 'like', "%{$q}%"));
        }
        if ($request->filled('type_besoin')) {
            $query->where('type_besoin', $request->input('type_besoin'));
        }
        if ($request->filled('statut')) {
            $query->where('statut', $request->input('statut'));
        }
        if ($request->filled('exercice_budgetaire_id')) {
            $query->where('exercice_budgetaire_id', $request->integer('exercice_budgetaire_id'));
        }

        if (! ExpressionBesoinPolicy::seesAll($request->user())) {
            $query->where(fn ($b) => $b->where('created_by', $request->user()->id)->orWhere('demandeur_id', $request->user()->id));
        }

        $besoins = $query->paginate(20)->appends($request->query());
        $exercices = ExerciceBudgetaire::query()->orderByDesc('annee')->get();

        // Compteurs par statut (mêmes filtres que la liste, hors filtre statut).
        $compteursQuery = ExpressionBesoin::query();
        if ($request->filled('q')) {
            $q = trim((string) $request->input('q'));
            $compteursQuery->where(fn ($b) => $b->where('reference', 'like', "%{$q}%")->orWhere('objet', 'like', "%{$q}%"));
        }
        if ($request->filled('type_besoin')) {
            $compteursQuery->where('type_besoin', $request->input('type_besoin'));
        }
        if ($request->filled('exercice_budgetaire_id')) {
            $compteursQuery->where('exercice_budgetaire_id', $request->integer('exercice_budgetaire_id'));
        }
        if (! ExpressionBesoinPolicy::seesAll($request->user())) {
            $compteursQuery->where(fn ($b) => $b->where('created_by', $request->user()->id)->orWhere('demandeur_id', $request->user()->id));
        }
        $parStatut = $compteursQuery->selectRaw('statut, COUNT(*) as total')->groupBy('statut')->pluck('total', 'statut');
        $compteurs = ['_total' => (int) $parStatut->sum()];
        foreach (StatutBesoin::cases() as $case) {
            $compteurs[$case->value] = (int) ($parStatut[$case->value] ?? 0);
        }

        return view('expression_besoins.index', compact('besoins', 'exercices', 'compteurs'));
    }

    public function archives(Request $request): View
    {
        $this->authorize('viewAny', ExpressionBesoin::class);

        $query = ExpressionBesoin::onlyTrashed()
            ->with(['exerciceBudgetaire', 'departement', 'createur'])
            ->orderByDesc('deleted_at');

        if ($request->filled('q')) {
            $q = trim((string) $request->input('q'));
            $query->where(fn ($b) => $b->where('reference', 'like', "%{$q}%")->orWhere('objet', 'like', "%{$q}%"));
        }
        if (! ExpressionBesoinPolicy::seesAll($request->user())) {
            $query->where(fn ($b) => $b->where('created_by', $request->user()->id)->orWhere('demandeur_id', $request->user()->id));
        }

        $besoins = $query->paginate(20)->appends($request->query());

        return view('expression_besoins.archives', compact('besoins'));
    }

    public function create(): View
    {
        return view('expression_besoins.create', $this->options());
    }

    public function store(StoreExpressionBesoinRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $lignes = $validated['lignes'];
        unset($validated['lignes']);

        $besoin = $this->service->create($validated, $lignes, $request->user());

        return redirect()
            ->route('expression-besoins.besoins.show', $besoin)
            ->with('success', __('Expression de besoin :ref créée.', ['ref' => $besoin->reference]));
    }

    public function show(
        ExpressionBesoin $expression_besoin,
        \App\Services\ExpressionBesoin\ExpressionBesoinWorkflowService $workflow,
        \App\Services\ExpressionBesoin\ExpressionBesoinAuditService $audit,
    ): View {
        $expression_besoin->load([
            'exerciceBudgetaire', 'departement', 'direction', 'serviceStructure', 'demandeur', 'createur',
            'pilier', 'axeStrategique', 'produit', 'sousProduit', 'activite', 'tache', 'indicateur',
            'lignes.ligneBudgetaire', 'lignes.sourceFinancement', 'lignes.activite',
            'validations.validateur', 'engagement',
        ]);

        // Synthèse budgétaire par ligne budgétaire utilisée.
        $budget = $expression_besoin->lignes
            ->filter(fn ($l) => $l->ligne_budgetaire_id !== null)
            ->groupBy('ligne_budgetaire_id')
            ->map(function ($lignes) {
                $ligne = $lignes->first()->ligneBudgetaire;

                return [
                    'ligne' => $ligne,
                    'demande' => (string) $lignes->sum(fn ($l) => (float) $l->montant),
                    'disponible' => $ligne?->creditDisponible() ?? '0',
                ];
            })->values();

        $attachments = $expression_besoin->gedAttachments()->with('document')->get();
        $etapeCourante = $workflow->currentPendingStep($expression_besoin);
        $peutDecider = $etapeCourante !== null && $workflow->userCanValidate(request()->user(), $etapeCourante);

        return view('expression_besoins.show', [
            'expressionBesoin' => $expression_besoin,
            'budget' => $budget,
            'attachments' => $attachments,
            'etapeCourante' => $etapeCourante,
            'peutDecider' => $peutDecider,
            'timeline' => $audit->timeline($expression_besoin),
        ]);
    }

    public function edit(ExpressionBesoin $expression_besoin): View
    {
        $expression_besoin->load('lignes');

        return view('expression_besoins.edit', array_merge(
            ['besoin' => $expression_besoin, 'documents' => $expression_besoin->gedDocuments()->get()],
            $this->options()
        ));
    }

    public function update(UpdateExpressionBesoinRequest $request, ExpressionBesoin $expression_besoin): RedirectResponse
    {
        $validated = $request->validated();
        $lignes = $validated['lignes'];
        unset($validated['lignes']);

        $this->service->update($expression_besoin, $validated, $lignes, $request->user());

        return redirect()
            ->route('expression-besoins.besoins.show', $expression_besoin)
            ->with('success', __('Expression de besoin mise à jour.'));
    }

    public function revise(Request $request, ExpressionBesoin $expression_besoin): RedirectResponse
    {
        $this->authorize('revise', $expression_besoin);

        $validated = $request->validate([
            'motif_revision' => ['required', 'string', 'max:1000'],
        ]);

        $this->service->revise($expression_besoin, $validated['motif_revision'], $request->user());

        return redirect()
            ->route('expression-besoins.besoins.show', $expression_besoin)
            ->with('success', __('Besoin remis en révision (retour au brouillon).'));
    }

    public function destroy(ExpressionBesoin $expression_besoin): RedirectResponse
    {
        $expression_besoin->delete();

        return redirect()
            ->route('expression-besoins.besoins.index')
            ->with('success', __('Expression de besoin supprimée.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function options(): array
    {
        return [
            'exercices' => ExerciceBudgetaire::query()->orderByDesc('annee')->get(),
            'departements' => Departement::query()->orderBy('libelle')->get(['id', 'libelle']),
            'directions' => Direction::query()->orderBy('libelle')->get(['id', 'libelle', 'departement_id']),
            'services' => Service::query()->orderBy('libelle')->get(['id', 'libelle', 'direction_id']),
            'demandeurs' => User::query()->orderBy('name')->get(['id', 'name']),
            'piliers' => Pilier::query()->orderBy('code')->get(['id', 'code', 'libelle']),
            'axes' => AxeStrategique::query()->orderBy('code')->get(['id', 'code', 'libelle', 'pilier_id']),
            'produits' => Produit::query()->orderBy('code')->get(['id', 'code', 'libelle', 'axe_strategique_id']),
            'sousProduits' => SousProduit::query()->orderBy('code')->get(['id', 'code', 'libelle', 'produit_id']),
            'activites' => Activite::query()->orderBy('code')->get(['id', 'code', 'libelle', 'sous_produit_id']),
            'taches' => Tache::query()->orderBy('code')->get(['id', 'code', 'libelle', 'activite_id']),
            'indicateurs' => Indicateur::query()->orderBy('code')->get(['id', 'code', 'libelle']),
            'lignesBudgetaires' => LigneBudgetaire::query()->orderBy('code')->get(['id', 'code', 'libelle', 'exercice_budgetaire_id']),
            'sourcesFinancement' => SourceFinancement::query()->orderBy('libelle')->get(['id', 'code', 'libelle']),
            'priorites' => PrioriteBesoin::cases(),
            'urgences' => NiveauUrgenceBesoin::cases(),
            'types' => TypeBesoin::cases(),
            'statuts' => StatutBesoin::cases(),
        ];
    }
}
