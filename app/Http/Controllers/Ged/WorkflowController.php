<?php

namespace App\Http\Controllers\Ged;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ged\StoreGedWorkflowRequest;
use App\Http\Requests\Ged\UpdateGedWorkflowRequest;
use App\Models\GedTypeDocument;
use App\Models\GedWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class WorkflowController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(GedWorkflow::class, 'workflow');
    }

    public function index(): View
    {
        $workflows = GedWorkflow::query()
            ->with('typeDocument')
            ->withCount('etapes')
            ->orderBy('libelle')
            ->paginate(20);

        return view('ged.workflows.index', compact('workflows'));
    }

    public function create(): View
    {
        return view('ged.workflows.create', ['types' => $this->types()]);
    }

    public function store(StoreGedWorkflowRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $etapes = $data['etapes'];
        unset($data['etapes']);
        $data['created_by'] = $request->user()->id;

        DB::transaction(function () use ($data, $etapes): void {
            $workflow = GedWorkflow::query()->create($data);
            $this->syncEtapes($workflow, $etapes);
        });

        return redirect()->route('ged.workflows.index')->with('success', __('Circuit de validation créé.'));
    }

    public function edit(GedWorkflow $workflow): View
    {
        $workflow->load('etapes');

        return view('ged.workflows.edit', ['workflow' => $workflow, 'types' => $this->types()]);
    }

    public function update(UpdateGedWorkflowRequest $request, GedWorkflow $workflow): RedirectResponse
    {
        $data = $request->validated();
        $etapes = $data['etapes'];
        unset($data['etapes']);
        $data['updated_by'] = $request->user()->id;

        DB::transaction(function () use ($workflow, $data, $etapes): void {
            $workflow->update($data);
            $workflow->etapes()->delete();
            $this->syncEtapes($workflow, $etapes);
        });

        return redirect()->route('ged.workflows.index')->with('success', __('Circuit de validation mis à jour.'));
    }

    public function destroy(GedWorkflow $workflow): RedirectResponse
    {
        $workflow->delete();

        return redirect()->route('ged.workflows.index')->with('success', __('Circuit archivé.'));
    }

    /**
     * @param  array<int, array<string, mixed>>  $etapes
     */
    private function syncEtapes(GedWorkflow $workflow, array $etapes): void
    {
        foreach (array_values($etapes) as $index => $etape) {
            $workflow->etapes()->create([
                'ordre' => $index + 1,
                'libelle' => $etape['libelle'],
                'type_etape' => $etape['type_etape'],
                'permission_requise' => $etape['permission_requise'] ?? null,
                'role_requis' => $etape['role_requis'] ?? null,
                'delai_jours' => $etape['delai_jours'] ?? null,
            ]);
        }
    }

    private function types()
    {
        return GedTypeDocument::query()->where('statut', 'actif')->orderBy('libelle')->get();
    }
}
