<table class="min-w-full text-sm">
    <thead class="bg-gray-50 text-gray-500">
        <tr>
            <th class="px-4 py-2 text-left">{{ __('Ligne') }}</th>
            <th class="px-4 py-2 text-left">{{ __('Colonne') }}</th>
            <th class="px-4 py-2 text-left">{{ __('Valeur') }}</th>
            <th class="px-4 py-2 text-left">{{ __('Règle') }}</th>
            <th class="px-4 py-2 text-left">{{ __('Correction attendue') }}</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-gray-100">
        @foreach ($issues as $issue)
            <tr>
                <td class="px-4 py-2 whitespace-nowrap">{{ $issue['ligne'] }}</td>
                <td class="px-4 py-2 whitespace-nowrap font-mono text-xs">{{ $issue['colonne'] }}</td>
                <td class="px-4 py-2 font-mono text-xs">{{ \Illuminate\Support\Str::limit((string) $issue['valeur'], 40) }}</td>
                <td class="px-4 py-2 text-gray-700">{{ $issue['regle'] }}</td>
                <td class="px-4 py-2 text-gray-500">{{ $issue['correction'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
