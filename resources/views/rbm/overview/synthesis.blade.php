<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">{{ __('Synthèse institutionnelle RBM/GAR') }}</h2>
            <a href="{{ route('rbm.export.synthesis-pdf') }}"><x-secondary-button>{{ __('Export PDF') }}</x-secondary-button></a>
        </div>
    </x-slot>
    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-6 shadow-sm rounded-lg text-sm">
            @foreach($piliers as $pilier)
                <h3 class="font-semibold text-indigo-700 mt-4">{{ $pilier->code }} — {{ $pilier->libelle }}</h3>
                <ul class="ml-4 list-disc">
                    @foreach($pilier->axesStrategiques as $axe)
                        <li>{{ $axe->libelle }} ({{ $axe->produits->count() }} produits)</li>
                    @endforeach
                </ul>
            @endforeach
        </div>
    </div>
</x-app-layout>