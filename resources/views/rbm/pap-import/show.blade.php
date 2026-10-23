<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Import PAP') }} #{{ $import->id }}</h2>
            <a href="{{ route('rbm.pap-import.history') }}"><x-secondary-button type="button">{{ __('Retour à l\'historique') }}</x-secondary-button></a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg text-sm">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg text-sm">{{ session('error') }}</div>
            @endif

            <div class="bg-white shadow-sm rounded-lg p-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div><span class="text-gray-500">{{ __('Statut') }} :</span> <x-pap-statut-badge :statut="$import->statut" /></div>
                <div><span class="text-gray-500">{{ __('Mode') }} :</span> {{ ucfirst(str_replace('_', ' ', $import->mode)) }}</div>
                <div><span class="text-gray-500">{{ __('Exercice') }} :</span> {{ $import->exerciceBudgetaire?->annee }}</div>
                <div><span class="text-gray-500">{{ __('Version') }} :</span> {{ $import->version }}</div>
                <div><span class="text-gray-500">{{ __('Cadre logique') }} :</span> {{ $import->cadreLogique?->code ?? '—' }}</div>
                <div><span class="text-gray-500">{{ __('Source') }} :</span> {{ $import->source_label }}</div>
                <div><span class="text-gray-500">{{ __('Utilisateur') }} :</span> {{ $import->user?->name }}</div>
                <div><span class="text-gray-500">{{ __('Adresse IP') }} :</span> {{ $import->adresse_ip ?? '—' }}</div>
                <div><span class="text-gray-500">{{ __('Empreinte fichier') }} :</span> <span class="font-mono text-xs">{{ $import->checksum ? \Illuminate\Support\Str::limit($import->checksum, 24) : '—' }}</span></div>
                <div><span class="text-gray-500">{{ __('Durée') }} :</span> {{ $import->duree_ms !== null ? $import->duree_ms.' ms' : '—' }}</div>
                <div><span class="text-gray-500">{{ __('Démarré') }} :</span> {{ $import->started_at?->format('d/m/Y H:i:s') }}</div>
                <div><span class="text-gray-500">{{ __('Terminé') }} :</span> {{ $import->finished_at?->format('d/m/Y H:i:s') }}</div>
                @if ($import->restoredFrom)
                    <div class="md:col-span-2 text-amber-700">{{ __('Restauration de l\'import #:id', ['id' => $import->restored_from_id]) }}</div>
                @endif
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach ([
                    ['Piliers', $import->nb_piliers], ['Axes', $import->nb_axes],
                    ['Produits', $import->nb_produits], ['Sous-produits', $import->nb_sous_produits],
                    ['Créations', $import->nb_creations], ['Mises à jour', $import->nb_mises_a_jour],
                    ['Suppressions', $import->nb_suppressions], ['Doublons', $import->nb_doublons],
                ] as [$label, $val])
                    <div class="bg-white shadow-sm rounded-lg p-4">
                        <div class="text-xl font-semibold text-gray-800">{{ number_format($val, 0, ',', ' ') }}</div>
                        <div class="text-xs text-gray-500 uppercase tracking-wide">{{ __($label) }}</div>
                    </div>
                @endforeach
            </div>

            <div class="bg-white shadow-sm rounded-lg p-6 flex items-center justify-between text-sm">
                <div>
                    <div class="text-gray-500 uppercase text-xs">{{ __('Montant total PAP') }}</div>
                    <div class="text-2xl font-semibold">{{ number_format($import->montant_total, 0, ',', ' ') }} FCFA</div>
                </div>
                @if ($import->mode === 'remplacement' && $import->snapshot && $import->statut === 'valide')
                    @canany(['pap.restore', 'rbm.admin'])
                        <form method="POST" action="{{ route('rbm.pap-import.restore', $import) }}"
                              onsubmit="return confirm('{{ __('Restaurer les données PAP de cet exercice à l\'état antérieur à cet import ?') }}');">
                            @csrf
                            <x-secondary-button>{{ __('Restaurer cette sauvegarde') }}</x-secondary-button>
                        </form>
                    @endcanany
                @endif
            </div>

            @php $issues = collect($import->rapport ?? []); @endphp
            @if ($issues->isNotEmpty())
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="px-6 py-3 border-b border-gray-100 font-semibold text-gray-700">{{ __('Rapport d\'anomalies (:n)', ['n' => $issues->count()]) }}</div>
                    @include('rbm.pap-import._issues', ['issues' => $issues])
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
