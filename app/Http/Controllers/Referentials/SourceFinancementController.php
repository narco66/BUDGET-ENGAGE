<?php

namespace App\Http\Controllers\Referentials;

use App\Http\Controllers\Controller;
use App\Http\Requests\Referentials\StoreSourceFinancementRequest;
use App\Http\Requests\Referentials\UpdateSourceFinancementRequest;
use App\Models\SourceFinancement;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SourceFinancementController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(SourceFinancement::class, 'source_financement');
    }

    public function index(): View
    {
        $sources = SourceFinancement::query()->orderBy('code')->paginate(15);

        return view('referentials.sources_financement.index', compact('sources'));
    }

    public function create(): View
    {
        return view('referentials.sources_financement.create');
    }

    public function store(StoreSourceFinancementRequest $request): RedirectResponse
    {
        SourceFinancement::query()->create($request->validated());

        return redirect()->route('referentials.sources-financement.index')->with('status', __('Source de financement créée.'));
    }

    public function show(SourceFinancement $source_financement): View
    {
        return view('referentials.sources_financement.show', ['source' => $source_financement]);
    }

    public function edit(SourceFinancement $source_financement): View
    {
        return view('referentials.sources_financement.edit', ['source' => $source_financement]);
    }

    public function update(UpdateSourceFinancementRequest $request, SourceFinancement $source_financement): RedirectResponse
    {
        $source_financement->update($request->validated());

        return redirect()->route('referentials.sources-financement.index')->with('status', __('Source de financement mise à jour.'));
    }

    public function destroy(SourceFinancement $source_financement): RedirectResponse
    {
        $source_financement->delete();

        return redirect()->route('referentials.sources-financement.index')->with('status', __('Source de financement supprimée.'));
    }
}
