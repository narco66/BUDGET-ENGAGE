<?php

namespace App\Http\Controllers\Referentials;

use App\Http\Controllers\Controller;
use App\Http\Requests\Referentials\StoreAgentRequest;
use App\Http\Requests\Referentials\UpdateAgentRequest;
use App\Models\Agent;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AgentController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Agent::class, 'agent');
    }

    public function index(): View
    {
        $agents = Agent::query()
            ->with('service.direction.departement')
            ->orderBy('nom')
            ->orderBy('prenom')
            ->paginate(15);

        return view('referentials.agents.index', compact('agents'));
    }

    public function create(): View
    {
        $services = Service::query()->with('direction.departement')->orderBy('libelle')->get();

        return view('referentials.agents.create', compact('services'));
    }

    public function store(StoreAgentRequest $request): RedirectResponse
    {
        Agent::query()->create($request->validated());

        return redirect()->route('referentials.agents.index')->with('status', __('Agent créé.'));
    }

    public function show(Agent $agent): View
    {
        $agent->load('service.direction.departement');

        return view('referentials.agents.show', compact('agent'));
    }

    public function edit(Agent $agent): View
    {
        $services = Service::query()->with('direction.departement')->orderBy('libelle')->get();

        return view('referentials.agents.edit', compact('agent', 'services'));
    }

    public function update(UpdateAgentRequest $request, Agent $agent): RedirectResponse
    {
        $agent->update($request->validated());

        return redirect()->route('referentials.agents.index')->with('status', __('Agent mis à jour.'));
    }

    public function destroy(Agent $agent): RedirectResponse
    {
        $agent->delete();

        return redirect()->route('referentials.agents.index')->with('status', __('Agent archivé.'));
    }
}
