<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">{{ __('Arborescence RBM/GAR') }}</h2></x-slot>
    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-6 shadow-sm rounded-lg text-sm space-y-4">
            @foreach($piliers as $pilier)
                <div>
                    <div class="font-semibold text-indigo-700">{{ $pilier->code }} — {{ $pilier->libelle }}</div>
                    @foreach($pilier->axesStrategiques as $axe)
                        <div class="ml-4 mt-1">
                            <div class="font-medium">{{ $axe->code }} — {{ $axe->libelle }}</div>
                            @foreach($axe->produits as $produit)
                                <div class="ml-4 text-gray-700">{{ $produit->code }} — {{ $produit->libelle }}</div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>