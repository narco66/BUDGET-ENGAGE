<?php

namespace App\Http\Controllers\Engagements;

use App\Http\Controllers\Controller;
use App\Http\Requests\Engagements\StoreEngagementTypeRequest;
use App\Http\Requests\Engagements\UpdateEngagementTypeRequest;
use App\Models\EngagementType;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EngagementTypeController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(EngagementType::class, 'engagement_type');
    }

    public function index(): View
    {
        $types = EngagementType::query()
            ->withCount('engagements')
            ->orderBy('ordre')
            ->orderBy('libelle')
            ->paginate(20);

        return view('engagements.types.index', compact('types'));
    }

    public function create(): View
    {
        return view('engagements.types.create');
    }

    public function store(StoreEngagementTypeRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = $request->user()->id;
        EngagementType::query()->create($data);

        return redirect()->route('engagements.types.index')->with('status', __('Type d\'engagement créé.'));
    }

    public function edit(EngagementType $engagement_type): View
    {
        return view('engagements.types.edit', ['type' => $engagement_type]);
    }

    public function update(UpdateEngagementTypeRequest $request, EngagementType $engagement_type): RedirectResponse
    {
        $data = $request->validated();
        $data['updated_by'] = $request->user()->id;
        $engagement_type->update($data);

        return redirect()->route('engagements.types.index')->with('status', __('Type d\'engagement mis à jour.'));
    }

    public function destroy(EngagementType $engagement_type): RedirectResponse
    {
        $engagement_type->delete();

        return redirect()->route('engagements.types.index')->with('status', __('Type d\'engagement archivé.'));
    }
}
