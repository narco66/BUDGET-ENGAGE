<x-app-layout>
    <x-slot name="header">
        <div class="page-header mb-0">
            <div>
                <nav class="page-breadcrumb mb-1">
                    <span>Rapports</span><span class="text-gray-300">/</span><span class="text-ceeac-primary font-semibold">{{ __('reports.activity.title') }}</span>
                </nav>
                <h2 class="page-title">{{ __('reports.activity.title') }}</h2>
            </div>
            <a href="{{ route('reports.activity.export.csv', request()->query()) }}" class="btn btn-secondary btn-sm">Export CSV</a>
        </div>
    </x-slot>
    <div class="space-y-4">
        <p class="text-sm text-gray-600">{{ __('reports.activity.subtitle') }}</p>
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('reports.activity.index') }}" class="grid md:grid-cols-3 gap-3">
                    <input type="text" class="form-input md:col-span-2" name="q" value="{{ request('q') }}" placeholder="Rechercher description/sujet" />
                    <input type="number" class="form-input" name="causer_id" value="{{ request('causer_id') }}" placeholder="ID utilisateur" />
                    <div class="md:col-span-3 flex gap-2">
                        <button class="btn btn-secondary btn-sm">Filtrer</button>
                        <a href="{{ route('reports.activity.index') }}" class="btn btn-ghost btn-sm">Réinitialiser</a>
                    </div>
                </form>
            </div>
        </div>
        <div class="card overflow-x-auto">
            <table class="data-table">
                <thead><tr>
                    <th class="whitespace-nowrap">{{ __('reports.activity.when') }}</th>
                    <th>{{ __('reports.activity.user') }}</th>
                    <th class="min-w-[240px]">{{ __('reports.activity.description') }}</th>
                    <th>{{ __('reports.activity.subject') }}</th>
                    <th class="max-w-xs">{{ __('reports.activity.properties') }}</th>
                </tr></thead>
                <tbody>
                    @forelse ($activities as $activity)
                        <tr class="align-top">
                            <td class="whitespace-nowrap text-gray-600">{{ $activity->created_at?->format('d/m/Y H:i:s') }}</td>
                            <td>{{ $activity->causer?->name ?? '—' }}</td>
                            <td>{{ $activity->description }}</td>
                            <td class="text-xs font-mono">
                                @if($activity->subject_type && $activity->subject_id)
                                    {{ class_basename($activity->subject_type) }} #{{ $activity->subject_id }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-xs text-gray-600 break-all max-w-md">
                                @if($activity->properties && count((array) $activity->properties) > 0)
                                    <pre class="whitespace-pre-wrap font-mono">{{ json_encode($activity->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">{{ __('reports.activity.empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div>{{ $activities->links() }}</div>
    </div>
</x-app-layout>
