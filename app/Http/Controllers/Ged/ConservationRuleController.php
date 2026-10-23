<?php

namespace App\Http\Controllers\Ged;

use App\Enums\SortFinalConservationGed;
use App\Http\Controllers\Controller;
use App\Http\Requests\Ged\StoreGedConservationRuleRequest;
use App\Http\Requests\Ged\UpdateGedConservationRuleRequest;
use App\Models\GedConservationRule;
use App\Models\GedTypeDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ConservationRuleController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(GedConservationRule::class, 'conservation_rule');
    }

    public function index(): View
    {
        $rules = GedConservationRule::query()
            ->with('typeDocument')
            ->orderBy('libelle')
            ->paginate(20);

        return view('ged.conservation.rules.index', compact('rules'));
    }

    public function create(): View
    {
        return view('ged.conservation.rules.create', $this->options());
    }

    public function store(StoreGedConservationRuleRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = $request->user()->id;
        GedConservationRule::query()->create($data);

        return redirect()->route('ged.conservation-rules.index')->with('success', __('Règle de conservation créée.'));
    }

    public function edit(GedConservationRule $conservationRule): View
    {
        return view('ged.conservation.rules.edit', array_merge(['rule' => $conservationRule], $this->options()));
    }

    public function update(UpdateGedConservationRuleRequest $request, GedConservationRule $conservationRule): RedirectResponse
    {
        $data = $request->validated();
        $data['updated_by'] = $request->user()->id;
        $conservationRule->update($data);

        return redirect()->route('ged.conservation-rules.index')->with('success', __('Règle de conservation mise à jour.'));
    }

    public function destroy(GedConservationRule $conservationRule): RedirectResponse
    {
        $conservationRule->delete();

        return redirect()->route('ged.conservation-rules.index')->with('success', __('Règle archivée.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function options(): array
    {
        return [
            'types' => GedTypeDocument::query()->where('statut', 'actif')->orderBy('libelle')->get(),
            'sortsFinaux' => SortFinalConservationGed::cases(),
        ];
    }
}
