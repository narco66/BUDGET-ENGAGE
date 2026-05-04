<?php

namespace App\Http\Controllers\Referentials;

use App\Http\Controllers\Controller;
use App\Http\Requests\Referentials\StoreDirectionRequest;
use App\Http\Requests\Referentials\UpdateDirectionRequest;
use App\Models\Departement;
use App\Models\Direction;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DirectionController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Direction::class, 'direction');
    }

    public function index(): View
    {
        $directions = Direction::query()
            ->with('departement')
            ->withCount('services')
            ->orderBy('departement_id')
            ->orderBy('code')
            ->paginate(15);

        return view('referentials.directions.index', compact('directions'));
    }

    public function create(): View
    {
        $departements = Departement::query()->orderBy('libelle')->get();

        return view('referentials.directions.create', compact('departements'));
    }

    public function store(StoreDirectionRequest $request): RedirectResponse
    {
        Direction::query()->create($request->validated());

        return redirect()->route('referentials.directions.index')->with('status', __('Direction créée.'));
    }

    public function show(Direction $direction): View
    {
        $direction->load(['departement']);
        $direction->loadCount('services');

        return view('referentials.directions.show', compact('direction'));
    }

    public function edit(Direction $direction): View
    {
        $departements = Departement::query()->orderBy('libelle')->get();

        return view('referentials.directions.edit', compact('direction', 'departements'));
    }

    public function update(UpdateDirectionRequest $request, Direction $direction): RedirectResponse
    {
        $direction->update($request->validated());

        return redirect()->route('referentials.directions.index')->with('status', __('Direction mise à jour.'));
    }

    public function destroy(Direction $direction): RedirectResponse
    {
        $direction->delete();

        return redirect()->route('referentials.directions.index')->with('status', __('Direction archivée.'));
    }
}
