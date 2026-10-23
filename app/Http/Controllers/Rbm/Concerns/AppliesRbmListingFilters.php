<?php

namespace App\Http\Controllers\Rbm\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait AppliesRbmListingFilters
{
    protected function applyListingFilters(Builder $query, Request $request, array $sortable = ['code', 'libelle', 'ordre', 'statut']): Builder
    {
        if ($request->filled('q')) {
            $query->recherche($request->string('q')->toString());
        }

        if ($request->filled('statut') && in_array($request->string('statut')->toString(), ['actif', 'inactif'], true)) {
            $query->where('statut', $request->string('statut')->toString());
        }

        if ($request->boolean('archived')) {
            $query->onlyTrashed();
        }

        $sort = $request->string('sort')->toString();
        $direction = $request->string('direction')->toString() === 'desc' ? 'desc' : 'asc';

        if (in_array($sort, $sortable, true)) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('ordre')->orderBy('code');
        }

        return $query;
    }
}
