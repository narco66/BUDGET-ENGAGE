<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Résultat de l\'import RBM/GAR') }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if ($success)
                <div class="bg-green-50 border border-green-200 text-green-800 p-4 rounded-lg">
                    {{ __('Import réussi : :count ligne(s) traitée(s).', ['count' => $imported]) }}
                </div>
            @else
                <div class="bg-red-50 border border-red-200 text-red-800 p-4 rounded-lg">
                    {{ __('Import rejeté : :count erreur(s) détectée(s). Aucune donnée n\'a été enregistrée.', ['count' => count($errors)]) }}
                </div>
            @endif

            @if ($errors !== [])
                <div class="bg-white p-6 shadow-sm rounded-lg">
                    <h3 class="font-medium text-gray-800 mb-3">{{ __('Rapport d\'erreurs') }}</h3>
                    <ul class="list-disc ml-5 text-sm text-red-700 space-y-1">
                        @foreach ($errors as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="flex gap-3">
                <a href="{{ route('rbm.import.index') }}"><x-secondary-button type="button">{{ __('Nouvel import') }}</x-secondary-button></a>
                <a href="{{ route('rbm.overview.tree') }}"><x-secondary-button type="button">{{ __('Voir l\'arborescence') }}</x-secondary-button></a>
            </div>
        </div>
    </div>
</x-app-layout>
