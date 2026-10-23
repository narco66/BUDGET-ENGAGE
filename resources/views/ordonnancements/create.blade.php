<x-app-layout>
    <x-slot name="header">
        <div class="page-header mb-0">
            <div>
                <nav class="page-breadcrumb mb-1">
                    <a href="{{ route('ordonnancements.index') }}" class="hover:text-ceeac-primary">{{ __('Ordonnancements') }}</a>
                    <span class="text-gray-300">/</span>
                    <span class="text-ceeac-primary font-semibold">{{ __('Création') }}</span>
                </nav>
                <h2 class="page-title">{{ __('Nouvel ordonnancement') }}</h2>
            </div>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto space-y-4">
        <div class="alert-info">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ __('Le contrôle financier, la GED et le circuit de validation se gèrent ensuite sur la fiche. Le mandat est généré après validation.') }}</span>
        </div>
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('ordonnancements.store') }}" class="space-y-6">@csrf
                    @include('ordonnancements._form')
                    <div class="flex flex-wrap items-center gap-3 pt-2 border-t border-gray-100">
                        <button class="btn btn-primary">{{ __('Enregistrer en brouillon') }}</button>
                        <a href="{{ route('ordonnancements.index') }}" class="btn btn-ghost">{{ __('Annuler') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
