<x-app-layout>
    <x-slot name="header">
        <div class="page-header mb-0">
            <div>
                <nav class="page-breadcrumb mb-1">
                    <a href="{{ route('ordonnancements.index') }}" class="hover:text-ceeac-primary">{{ __('Ordonnancements') }}</a>
                    <span class="text-gray-300">/</span>
                    <span class="text-ceeac-primary font-semibold">{{ __('Types') }}</span>
                </nav>
                <h2 class="page-title">{{ __('Types d’ordonnancement') }}</h2>
            </div>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto space-y-4">
        @if (session('status'))<div class="alert-success">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="alert-danger"><ul class="list-disc list-inside text-sm">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

        <div class="card">
            <div class="card-body p-0">
                <table class="data-table">
                    <thead><tr><th>{{ __('Code') }}</th><th>{{ __('Libellé') }}</th><th>{{ __('Famille') }}</th><th>{{ __('Mandat') }}</th><th>{{ __('Actif') }}</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($types as $type)
                            <tr>
                                <td class="font-mono text-xs">{{ $type->code }}</td>
                                <td>{{ $type->libelle }}</td>
                                <td class="text-sm">{{ $type->famille ?? '—' }}</td>
                                <td>{!! $type->exige_mandat ? '<span class="badge badge-info">Exigé</span>' : '<span class="badge badge-neutral">Non</span>' !!}</td>
                                <td>{!! $type->est_actif ? '<span class="badge badge-success">Oui</span>' : '<span class="badge badge-danger">Non</span>' !!}</td>
                                <td class="text-right">
                                    <form method="POST" action="{{ route('ordonnancements.types.update', $type) }}" class="inline">@csrf @method('PUT')
                                        <input type="hidden" name="code" value="{{ $type->code }}">
                                        <input type="hidden" name="libelle" value="{{ $type->libelle }}">
                                        <input type="hidden" name="famille" value="{{ $type->famille }}">
                                        <input type="hidden" name="exige_mandat" value="{{ $type->exige_mandat ? 1 : 0 }}">
                                        <input type="hidden" name="est_actif" value="{{ $type->est_actif ? 0 : 1 }}">
                                        <button class="text-ceeac-primary hover:underline text-sm">{{ $type->est_actif ? __('Désactiver') : __('Activer') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h3 class="font-semibold mb-3">{{ __('Nouveau type') }}</h3>
                <form method="POST" action="{{ route('ordonnancements.types.store') }}" class="grid grid-cols-1 md:grid-cols-2 gap-3">@csrf
                    <div>
                        <x-input-label for="code" :value="__('Code')" />
                        <x-text-input id="code" name="code" class="block mt-1 w-full" maxlength="60" :value="old('code')" required />
                    </div>
                    <div>
                        <x-input-label for="libelle" :value="__('Libellé')" />
                        <x-text-input id="libelle" name="libelle" class="block mt-1 w-full" :value="old('libelle')" required />
                    </div>
                    <div>
                        <x-input-label for="famille" :value="__('Famille')" />
                        <x-text-input id="famille" name="famille" class="block mt-1 w-full" maxlength="60" :value="old('famille')" />
                    </div>
                    <div class="flex items-end gap-4">
                        <label class="inline-flex items-center gap-1 text-sm"><input type="checkbox" name="exige_mandat" value="1" checked class="form-checkbox"> {{ __('Exige mandat') }}</label>
                        <label class="inline-flex items-center gap-1 text-sm"><input type="checkbox" name="est_actif" value="1" checked class="form-checkbox"> {{ __('Actif') }}</label>
                    </div>
                    <div class="md:col-span-2"><button class="btn btn-primary">{{ __('Créer le type') }}</button></div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
