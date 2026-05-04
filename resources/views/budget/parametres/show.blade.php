<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl font-mono">{{ $parametre_controle->cle }}</h2></x-slot>
    <div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
        <div class="bg-white shadow-sm sm:rounded-lg p-6 text-sm space-y-2 font-mono">
            <div><span class="text-gray-600 not-italic font-sans">{{ __('Type') }} :</span> {{ $parametre_controle->type_valeur }}</div>
            <div class="whitespace-pre-wrap"><span class="text-gray-600 not-italic font-sans">{{ __('Valeur') }} :</span><br>{{ $parametre_controle->valeur }}</div>
            @if($parametre_controle->description)
                <div class="font-sans text-gray-700">{{ $parametre_controle->description }}</div>
            @endif
            <div class="font-sans">{{ __('Actif') }} : {{ $parametre_controle->actif ? __('Oui') : __('Non') }}</div>
        </div>
        <a href="{{ route('budget.parametres.index') }}" class="text-sm text-indigo-600">{{ __('← Liste') }}</a>
    </div>
</x-app-layout>
