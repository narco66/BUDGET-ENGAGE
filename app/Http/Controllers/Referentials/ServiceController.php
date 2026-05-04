<?php

namespace App\Http\Controllers\Referentials;

use App\Http\Controllers\Controller;
use App\Http\Requests\Referentials\StoreServiceRequest;
use App\Http\Requests\Referentials\UpdateServiceRequest;
use App\Models\Direction;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Service::class, 'service');
    }

    public function index(): View
    {
        $services = Service::query()
            ->with('direction.departement')
            ->orderBy('direction_id')
            ->orderBy('code')
            ->paginate(15);

        return view('referentials.services.index', compact('services'));
    }

    public function create(): View
    {
        $directions = Direction::query()->with('departement')->orderBy('libelle')->get();

        return view('referentials.services.create', compact('directions'));
    }

    public function store(StoreServiceRequest $request): RedirectResponse
    {
        Service::query()->create($request->validated());

        return redirect()->route('referentials.services.index')->with('status', __('Service créé.'));
    }

    public function show(Service $service): View
    {
        $service->load('direction.departement');

        return view('referentials.services.show', compact('service'));
    }

    public function edit(Service $service): View
    {
        $directions = Direction::query()->with('departement')->orderBy('libelle')->get();

        return view('referentials.services.edit', compact('service', 'directions'));
    }

    public function update(UpdateServiceRequest $request, Service $service): RedirectResponse
    {
        $service->update($request->validated());

        return redirect()->route('referentials.services.index')->with('status', __('Service mis à jour.'));
    }

    public function destroy(Service $service): RedirectResponse
    {
        $service->delete();

        return redirect()->route('referentials.services.index')->with('status', __('Service archivé.'));
    }
}
