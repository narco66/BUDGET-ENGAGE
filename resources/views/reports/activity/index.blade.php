<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">{{ __('reports.activity.title') }}</h2>
    </x-slot>
    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
        <p class="text-sm text-gray-600">{{ __('reports.activity.subtitle') }}</p>
        <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
            <table class="min-w-full text-sm divide-y divide-gray-200">
                <thead class="bg-gray-50"><tr>
                    <th class="px-4 py-3 text-left whitespace-nowrap">{{ __('reports.activity.when') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('reports.activity.user') }}</th>
                    <th class="px-4 py-3 text-left min-w-[240px]">{{ __('reports.activity.description') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('reports.activity.subject') }}</th>
                    <th class="px-4 py-3 text-left max-w-xs">{{ __('reports.activity.properties') }}</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($activities as $activity)
                        <tr class="align-top">
                            <td class="px-4 py-3 whitespace-nowrap text-gray-600">{{ $activity->created_at?->format('d/m/Y H:i:s') }}</td>
                            <td class="px-4 py-3">{{ $activity->causer?->name ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $activity->description }}</td>
                            <td class="px-4 py-3 text-xs font-mono">
                                @if($activity->subject_type && $activity->subject_id)
                                    {{ class_basename($activity->subject_type) }} #{{ $activity->subject_id }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-600 break-all max-w-md">
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
        {{ $activities->links() }}
    </div>
</x-app-layout>
