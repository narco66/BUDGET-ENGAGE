<x-app-layout>
    <x-slot name="header">
        <div class="page-header mb-0">
            <div>
                <nav class="page-breadcrumb mb-1">
                    <a href="{{ route('paiements.index') }}" class="hover:text-ceeac-primary">{{ __('Paiements') }}</a>
                    <span class="text-gray-300">/</span>
                    <span class="text-ceeac-primary font-semibold">{{ __('Banques') }}</span>
                </nav>
                <h2 class="page-title">{{ __('Comptes bancaires de décaissement') }}</h2>
            </div>
        </div>
    </x-slot>

    @php($fmt = fn ($v) => $v === null ? '—' : number_format((float) $v, 2, ',', ' '))

    <div class="max-w-5xl mx-auto space-y-4">
        @if (session('status'))<div class="alert-success">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="alert-danger"><ul class="list-disc list-inside text-sm">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

        <div class="card">
            <div class="card-body p-0">
                <table class="data-table">
                    <thead><tr><th>{{ __('Code') }}</th><th>{{ __('Compte') }}</th><th>{{ __('BIC/SWIFT') }}</th><th>{{ __('Devise') }}</th><th class="text-right">{{ __('Disponible') }}</th><th class="text-right">{{ __('Plafond restant') }}</th><th>{{ __('Actif') }}</th></tr></thead>
                    <tbody>
                        @foreach ($banques as $b)
                            <tr>
                                <td class="font-mono text-xs">{{ $b->code }}</td>
                                <td class="text-sm">{{ $b->libelle }} <span class="text-gray-400">{{ $b->banque }}</span></td>
                                <td class="font-mono text-xs">{{ $b->bic_swift ?? '—' }}</td>
                                <td>{{ $b->devise }}</td>
                                <td class="text-right font-mono tabular-nums">{{ $fmt($soldes[$b->id]['disponible'] ?? null) }}</td>
                                <td class="text-right font-mono tabular-nums">{{ $fmt($soldes[$b->id]['plafond_restant'] ?? null) }}</td>
                                <td>{!! $b->est_actif ? '<span class="badge badge-success">Oui</span>' : '<span class="badge badge-danger">Non</span>' !!}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h3 class="font-semibold mb-3">{{ __('Nouveau compte bancaire') }}</h3>
                <form method="POST" action="{{ route('paiements.banques.store') }}" class="grid grid-cols-1 md:grid-cols-2 gap-3">@csrf
                    <div>
                        <x-input-label for="code" :value="__('Code')" />
                        <x-text-input id="code" name="code" class="block mt-1 w-full" maxlength="60" :value="old('code')" required />
                    </div>
                    <div>
                        <x-input-label for="libelle" :value="__('Libellé')" />
                        <x-text-input id="libelle" name="libelle" class="block mt-1 w-full" :value="old('libelle')" required />
                    </div>
                    <div>
                        <x-input-label for="banque" :value="__('Banque')" />
                        <x-text-input id="banque" name="banque" class="block mt-1 w-full" maxlength="150" :value="old('banque')" />
                    </div>
                    <div>
                        <x-input-label for="bic_swift" :value="__('BIC / SWIFT')" />
                        <x-text-input id="bic_swift" name="bic_swift" class="block mt-1 w-full" maxlength="20" :value="old('bic_swift')" />
                    </div>
                    <div>
                        <x-input-label for="iban" :value="__('IBAN')" />
                        <x-text-input id="iban" name="iban" class="block mt-1 w-full" maxlength="60" :value="old('iban')" />
                    </div>
                    <div>
                        <x-input-label for="devise" :value="__('Devise')" />
                        <x-text-input id="devise" name="devise" class="block mt-1 w-full" maxlength="10" :value="old('devise', 'XAF')" />
                    </div>
                    <div>
                        <x-input-label for="plafond_decaissement" :value="__('Plafond de décaissement')" />
                        <x-text-input id="plafond_decaissement" type="number" step="0.01" min="0" name="plafond_decaissement" class="block mt-1 w-full" :value="old('plafond_decaissement')" />
                    </div>
                    <div>
                        <x-input-label for="solde_initial" :value="__('Solde initial')" />
                        <x-text-input id="solde_initial" type="number" step="0.01" name="solde_initial" class="block mt-1 w-full" :value="old('solde_initial')" />
                    </div>
                    <div class="md:col-span-2"><button class="btn btn-primary">{{ __('Créer le compte') }}</button></div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
