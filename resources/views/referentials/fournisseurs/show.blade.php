<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">{{ $fournisseur->raison_sociale }}</h2></x-slot>
    <div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
        <div class="bg-white shadow-sm sm:rounded-lg p-6 text-sm space-y-2">
            <div>{{ __('Sigle') }} : {{ $fournisseur->sigle ?? '—' }}</div>
            <div>{{ __('Statut') }} : {{ $fournisseur->statut }}</div>
            @if($fournisseur->email)
                <div>{{ __('E-mail') }} : {{ $fournisseur->email }}</div>
            @endif
            @if($fournisseur->telephone)
                <div>{{ __('Téléphone') }} : {{ $fournisseur->telephone }}</div>
            @endif
        </div>
        <a href="{{ route('referentials.fournisseurs.index') }}" class="text-sm text-indigo-600">{{ __('← Liste') }}</a>
    </div>
</x-app-layout>
