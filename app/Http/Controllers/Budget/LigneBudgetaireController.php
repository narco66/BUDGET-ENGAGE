<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use App\Http\Requests\Budget\StoreLigneBudgetaireRequest;
use App\Http\Requests\Budget\UpdateLigneBudgetaireRequest;
use App\Models\Departement;
use App\Models\Direction;
use App\Models\ExerciceBudgetaire;
use App\Models\LigneBudgetaire;
use App\Models\NatureDepense;
use App\Models\Service;
use App\Models\SourceFinancement;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LigneBudgetaireController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(LigneBudgetaire::class, 'ligne_budgetaire');
    }

    public function index(): View
    {
        $lignes = LigneBudgetaire::query()
            ->with(['exerciceBudgetaire', 'natureDepense', 'departement'])
            ->orderByDesc('exercice_budgetaire_id')
            ->orderBy('code')
            ->paginate(15);

        return view('budget.lignes.index', compact('lignes'));
    }

    public function create(): View
    {
        return view('budget.lignes.create', $this->formDependencies());
    }

    public function store(StoreLigneBudgetaireRequest $request): RedirectResponse
    {
        LigneBudgetaire::query()->create($this->normalizedMontants($request->validated()));

        return redirect()->route('budget.lignes.index')->with('status', __('Ligne budgétaire créée.'));
    }

    public function show(LigneBudgetaire $ligne_budgetaire): View
    {
        $ligne_budgetaire->load([
            'exerciceBudgetaire',
            'departement',
            'direction',
            'service',
            'natureDepense',
            'sourceFinancement',
        ]);

        return view('budget.lignes.show', compact('ligne_budgetaire'));
    }

    public function edit(LigneBudgetaire $ligne_budgetaire): View
    {
        return view('budget.lignes.edit', array_merge(
            ['ligne' => $ligne_budgetaire],
            $this->formDependencies()
        ));
    }

    public function update(UpdateLigneBudgetaireRequest $request, LigneBudgetaire $ligne_budgetaire): RedirectResponse
    {
        $ligne_budgetaire->update($this->normalizedMontants($request->validated()));

        return redirect()->route('budget.lignes.index')->with('status', __('Ligne budgétaire mise à jour.'));
    }

    public function destroy(LigneBudgetaire $ligne_budgetaire): RedirectResponse
    {
        $ligne_budgetaire->delete();

        return redirect()->route('budget.lignes.index')->with('status', __('Ligne budgétaire archivée.'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function formDependencies(): array
    {
        return [
            'exercices' => ExerciceBudgetaire::query()->orderByDesc('annee')->get(),
            'departements' => Departement::query()->orderBy('libelle')->get(),
            'directions' => Direction::query()->with('departement')->orderBy('libelle')->get(),
            'services' => Service::query()->with('direction.departement')->orderBy('libelle')->get(),
            'natures' => NatureDepense::query()->orderBy('code')->get(),
            'sources' => SourceFinancement::query()->orderBy('code')->get(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function normalizedMontants(array $data): array
    {
        foreach (['montant_engage', 'montant_liquide', 'montant_ordonnance', 'montant_paye'] as $field) {
            $data[$field] = $data[$field] ?? 0;
        }

        return $data;
    }
}
