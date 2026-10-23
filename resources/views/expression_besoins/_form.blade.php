@props([
    'besoin' => null,
    'exercices' => collect(), 'departements' => collect(), 'directions' => collect(), 'services' => collect(),
    'demandeurs' => collect(), 'piliers' => collect(), 'axes' => collect(), 'produits' => collect(),
    'sousProduits' => collect(), 'activites' => collect(), 'taches' => collect(), 'indicateurs' => collect(),
    'lignesBudgetaires' => collect(), 'sourcesFinancement' => collect(),
    'priorites' => [], 'urgences' => [], 'documents' => collect(),
])
@php($b = $besoin)
@php($action = $b ? route('expression-besoins.besoins.update', $b) : route('expression-besoins.besoins.store'))
@php($initialLignes = old('lignes', $b?->lignes?->map(fn ($l) => [
    'designation' => $l->designation, 'code_article' => $l->code_article, 'categorie' => $l->categorie,
    'specifications' => $l->specifications, 'quantite' => (float) $l->quantite, 'unite' => $l->unite,
    'prix_unitaire' => (float) $l->prix_unitaire, 'priorite' => $l->priorite,
    'echeance' => optional($l->echeance)->format('Y-m-d'),
    'ligne_budgetaire_id' => $l->ligne_budgetaire_id, 'source_financement_id' => $l->source_financement_id,
    'activite_id' => $l->activite_id, 'observation' => $l->observation,
])->values()->all() ?: [['designation' => '', 'code_article' => '', 'categorie' => '', 'specifications' => '', 'quantite' => 1, 'unite' => '', 'prix_unitaire' => 0, 'priorite' => '', 'echeance' => '', 'ligne_budgetaire_id' => '', 'source_financement_id' => '', 'activite_id' => '', 'observation' => '']]))
@php($steps = [
    1 => __('Informations générales'), 2 => __('Rattachement'), 3 => __('Détail du besoin'),
    4 => __('Estimation financière'), 5 => __('Justification & pièces'), 6 => __('Validation'),
])

<div x-data="{
        step: 1,
        type: @js(old('type_besoin', $b?->type_besoin?->value ?? 'pap')),
        f: {
            pilier_id: @js((string) old('pilier_id', $b?->pilier_id ?? '')),
            axe_strategique_id: @js((string) old('axe_strategique_id', $b?->axe_strategique_id ?? '')),
            produit_id: @js((string) old('produit_id', $b?->produit_id ?? '')),
            sous_produit_id: @js((string) old('sous_produit_id', $b?->sous_produit_id ?? '')),
            activite_id: @js((string) old('activite_id', $b?->activite_id ?? '')),
        },
        axes: @js($axes), produits: @js($produits), sousProduits: @js($sousProduits), taches: @js($taches),
        lignes: @js($initialLignes),
        next() { if (this.step < 6) this.step++; window.scrollTo({top:0,behavior:'smooth'}); },
        prev() { if (this.step > 1) this.step--; window.scrollTo({top:0,behavior:'smooth'}); },
        goto(n) { this.step = n; window.scrollTo({top:0,behavior:'smooth'}); },
        axesF() { return this.axes.filter(a => String(a.pilier_id) === this.f.pilier_id); },
        produitsF() { return this.produits.filter(p => String(p.axe_strategique_id) === this.f.axe_strategique_id); },
        sousProduitsF() { return this.sousProduits.filter(s => String(s.produit_id) === this.f.produit_id); },
        tachesF() { return this.taches.filter(t => String(t.activite_id) === this.f.activite_id); },
        resetFrom(level) {
            const order = ['pilier_id','axe_strategique_id','produit_id','sous_produit_id','activite_id'];
            let i = order.indexOf(level) + 1;
            for (; i < order.length; i++) this.f[order[i]] = '';
        },
        addLigne() { this.lignes.push({ designation:'', code_article:'', categorie:'', specifications:'', quantite:1, unite:'', prix_unitaire:0, priorite:'', echeance:'', ligne_budgetaire_id:'', source_financement_id:'', activite_id:'', observation:'' }); },
        removeLigne(i) { if (this.lignes.length > 1) this.lignes.splice(i, 1); },
        montant(l) { return (parseFloat(l.quantite)||0) * (parseFloat(l.prix_unitaire)||0); },
        total() { return this.lignes.reduce((s,l) => s + this.montant(l), 0); },
        catTotal(c) { return this.lignes.filter(l => l.categorie===c).reduce((s,l)=>s+this.montant(l),0); },
        tva() { return this.total() * 0.18; },
        ttc() { return this.total() * 1.18; },
        nbLignes() { return this.lignes.filter(l => (l.designation||'').trim() !== '').length; },
        fmt(v) { return new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 0 }).format(Math.round(v||0)); }
    }" class="space-y-5">

    {{-- ===================== STEPPER ===================== --}}
    <x-ds.card>
        <ol class="flex flex-wrap items-center gap-y-3">
            @foreach ($steps as $n => $label)
                <li class="flex items-center {{ $n < count($steps) ? 'flex-1 min-w-[140px]' : '' }}">
                    <button type="button" @click="goto({{ $n }})" class="flex items-center gap-2 text-left">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-bold transition"
                              :class="step==={{ $n }} ? 'bg-ceeac-primary text-white' : (step>{{ $n }} ? 'bg-emerald-500 text-white' : 'bg-gray-100 text-gray-500')">
                            <span x-show="step<={{ $n }}">{{ $n }}</span>
                            <span x-show="step>{{ $n }}" x-cloak>✓</span>
                        </span>
                        <span class="text-sm font-medium hidden sm:inline" :class="step==={{ $n }} ? 'text-ceeac-primary' : 'text-gray-500'">{{ $label }}</span>
                    </button>
                    @if ($n < count($steps))
                        <span class="flex-1 mx-2 h-px bg-gray-200 hidden sm:block"></span>
                    @endif
                </li>
            @endforeach
        </ol>
    </x-ds.card>

    @if ($errors->any())
        <x-ds.alert type="danger">
            <p class="font-semibold">{{ __('Veuillez corriger les erreurs suivantes :') }}</p>
            <ul class="list-disc list-inside text-sm mt-1">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </x-ds.alert>
    @endif

    <form method="POST" action="{{ $action }}" class="space-y-5">
        @csrf
        @if ($b) @method('PUT') @endif
        <input type="hidden" name="type_besoin" :value="type" />

        {{-- ÉTAPE 1 : Informations générales --}}
        <x-ds.card x-show="step===1">
            <x-ds.section-heading :title="__('Informations générales')" :description="__('Identité de la structure demandeuse et objet du besoin.')" />
            <div class="grid md:grid-cols-3 gap-4 mt-4">
                <div>
                    <x-input-label :value="__('Type de besoin')" />
                    <select x-model="type" class="form-select mt-1">
                        <option value="pap">{{ __('Besoin PAP') }}</option>
                        <option value="hors_pap">{{ __('Besoin Hors PAP') }}</option>
                    </select>
                </div>
                <div>
                    <x-input-label for="exercice_budgetaire_id" :value="__('Exercice budgétaire')" />
                    <select id="exercice_budgetaire_id" name="exercice_budgetaire_id" class="form-select mt-1" required>
                        @foreach ($exercices as $ex)
                            <option value="{{ $ex->id }}" @selected((int) old('exercice_budgetaire_id', $b?->exercice_budgetaire_id) === $ex->id)>{{ $ex->annee }}</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('exercice_budgetaire_id')" />
                </div>
                <div>
                    <x-input-label for="date_besoin" :value="__('Date du besoin')" />
                    <x-text-input id="date_besoin" name="date_besoin" type="date" class="block mt-1 w-full" :value="old('date_besoin', $b?->date_besoin?->format('Y-m-d'))" />
                </div>
                <div>
                    <x-input-label for="structure_demandeuse" :value="__('Structure / Unité administrative')" />
                    <x-text-input id="structure_demandeuse" name="structure_demandeuse" class="block mt-1 w-full" :value="old('structure_demandeuse', $b?->structure_demandeuse)" />
                </div>
                <div>
                    <x-input-label for="departement_id" :value="__('Département')" />
                    <select id="departement_id" name="departement_id" class="form-select mt-1">
                        <option value="">—</option>
                        @foreach ($departements as $d)<option value="{{ $d->id }}" @selected((int) old('departement_id', $b?->departement_id) === $d->id)>{{ $d->libelle }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="direction_id" :value="__('Direction')" />
                    <select id="direction_id" name="direction_id" class="form-select mt-1">
                        <option value="">—</option>
                        @foreach ($directions as $d)<option value="{{ $d->id }}" @selected((int) old('direction_id', $b?->direction_id) === $d->id)>{{ $d->libelle }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="service_id" :value="__('Service')" />
                    <select id="service_id" name="service_id" class="form-select mt-1">
                        <option value="">—</option>
                        @foreach ($services as $s)<option value="{{ $s->id }}" @selected((int) old('service_id', $b?->service_id) === $s->id)>{{ $s->libelle }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="demandeur_id" :value="__('Demandeur')" />
                    <select id="demandeur_id" name="demandeur_id" class="form-select mt-1">
                        <option value="">— ({{ __('moi') }})</option>
                        @foreach ($demandeurs as $u)<option value="{{ $u->id }}" @selected((int) old('demandeur_id', $b?->demandeur_id) === $u->id)>{{ $u->name }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="priorite" :value="__('Priorité')" />
                    <select id="priorite" name="priorite" class="form-select mt-1">
                        @foreach ($priorites as $p)<option value="{{ $p->value }}" @selected(old('priorite', $b?->priorite?->value ?? 'normale') === $p->value)>{{ $p->libelle() }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="niveau_urgence" :value="__('Niveau d\'urgence')" />
                    <select id="niveau_urgence" name="niveau_urgence" class="form-select mt-1">
                        @foreach ($urgences as $u)<option value="{{ $u->value }}" @selected(old('niveau_urgence', $b?->niveau_urgence?->value ?? 'normal') === $u->value)>{{ $u->libelle() }}</option>@endforeach
                    </select>
                </div>
                <div class="md:col-span-3">
                    <x-input-label for="objet" :value="__('Intitulé / Objet du besoin')" />
                    <x-text-input id="objet" name="objet" class="block mt-1 w-full" :value="old('objet', $b?->objet)" required />
                    <x-input-error class="mt-2" :messages="$errors->get('objet')" />
                </div>
                <div class="md:col-span-3">
                    <x-input-label for="description" :value="__('Description')" />
                    <textarea id="description" name="description" rows="3" class="form-input mt-1">{{ old('description', $b?->description) }}</textarea>
                </div>
            </div>
        </x-ds.card>

        {{-- ÉTAPE 2 : Rattachement --}}
        <x-ds.card x-show="step===2" x-cloak>
            <x-ds.section-heading :title="__('Rattachement stratégique')" :description="__('Lien avec le PAP ou cadre hors PAP.')" />
            <div x-show="type==='pap'" class="grid md:grid-cols-3 gap-4 mt-4">
                <div>
                    <x-input-label :value="__('Pilier')" />
                    <select name="pilier_id" x-model="f.pilier_id" @change="resetFrom('pilier_id')" class="form-select mt-1">
                        <option value="">—</option>
                        @foreach ($piliers as $p)<option value="{{ $p->id }}">{{ $p->code }} — {{ $p->libelle }}</option>@endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('pilier_id')" />
                </div>
                <div>
                    <x-input-label :value="__('Axe stratégique')" />
                    <select name="axe_strategique_id" x-model="f.axe_strategique_id" @change="resetFrom('axe_strategique_id')" class="form-select mt-1">
                        <option value="">—</option>
                        <template x-for="a in axesF()" :key="a.id"><option :value="a.id" x-text="a.code + ' — ' + a.libelle"></option></template>
                    </select>
                </div>
                <div>
                    <x-input-label :value="__('Produit')" />
                    <select name="produit_id" x-model="f.produit_id" @change="resetFrom('produit_id')" class="form-select mt-1">
                        <option value="">—</option>
                        <template x-for="p in produitsF()" :key="p.id"><option :value="p.id" x-text="p.code + ' — ' + p.libelle"></option></template>
                    </select>
                </div>
                <div>
                    <x-input-label :value="__('Sous-produit')" />
                    <select name="sous_produit_id" x-model="f.sous_produit_id" @change="resetFrom('sous_produit_id')" class="form-select mt-1">
                        <option value="">—</option>
                        <template x-for="s in sousProduitsF()" :key="s.id"><option :value="s.id" x-text="s.code + ' — ' + s.libelle"></option></template>
                    </select>
                </div>
                <div>
                    <x-input-label :value="__('Activité')" />
                    <select name="activite_id" x-model="f.activite_id" @change="resetFrom('activite_id')" class="form-select mt-1">
                        <option value="">—</option>
                        @foreach ($activites as $a)
                            <template x-if="String(@js($a->sous_produit_id)) === f.sous_produit_id">
                                <option value="{{ $a->id }}">{{ $a->code }} — {{ $a->libelle }}</option>
                            </template>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('activite_id')" />
                </div>
                <div>
                    <x-input-label :value="__('Tâche')" />
                    <select name="tache_id" class="form-select mt-1">
                        <option value="">—</option>
                        <template x-for="t in tachesF()" :key="t.id"><option :value="t.id" x-text="t.code + ' — ' + t.libelle" :selected="String(t.id)===@js((string) old('tache_id', $b?->tache_id ?? ''))"></option></template>
                    </select>
                </div>
                <div>
                    <x-input-label :value="__('Indicateur')" />
                    <select name="indicateur_id" class="form-select mt-1">
                        <option value="">—</option>
                        @foreach ($indicateurs as $i)<option value="{{ $i->id }}" @selected((int) old('indicateur_id', $b?->indicateur_id) === $i->id)>{{ $i->code }} — {{ $i->libelle }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="responsable_activite" :value="__('Responsable de l\'activité')" />
                    <x-text-input id="responsable_activite" name="responsable_activite" class="block mt-1 w-full" :value="old('responsable_activite', $b?->responsable_activite)" />
                </div>
            </div>

            <div x-show="type==='hors_pap'" x-cloak class="grid md:grid-cols-3 gap-4 mt-4">
                <div>
                    <x-input-label for="categorie_hors_pap" :value="__('Catégorie')" />
                    <select id="categorie_hors_pap" name="categorie_hors_pap" class="form-select mt-1">
                        <option value="">—</option>
                        @foreach (App\Enums\CategorieBesoinHorsPap::cases() as $cat)
                            <option value="{{ $cat->value }}" @selected(old('categorie_hors_pap', $b?->categorie_hors_pap?->value) === $cat->value)>{{ $cat->libelle() }}</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('categorie_hors_pap')" />
                </div>
                <div>
                    <x-input-label for="sous_categorie" :value="__('Sous-catégorie')" />
                    <x-text-input id="sous_categorie" name="sous_categorie" class="block mt-1 w-full" :value="old('sous_categorie', $b?->sous_categorie)" />
                </div>
                <div>
                    <x-input-label for="nature_besoin" :value="__('Nature du besoin')" />
                    <x-text-input id="nature_besoin" name="nature_besoin" class="block mt-1 w-full" :value="old('nature_besoin', $b?->nature_besoin)" />
                </div>
                <div>
                    <x-input-label for="centre_cout" :value="__('Centre de coût')" />
                    <x-text-input id="centre_cout" name="centre_cout" class="block mt-1 w-full" :value="old('centre_cout', $b?->centre_cout)" />
                </div>
                <div>
                    <x-input-label for="centre_responsabilite" :value="__('Centre de responsabilité')" />
                    <x-text-input id="centre_responsabilite" name="centre_responsabilite" class="block mt-1 w-full" :value="old('centre_responsabilite', $b?->centre_responsabilite)" />
                </div>
            </div>
        </x-ds.card>

        {{-- ÉTAPE 3 : Détail du besoin (lignes) --}}
        <x-ds.card x-show="step===3" x-cloak>
            <div class="flex items-center justify-between">
                <x-ds.section-heading :title="__('Détail du besoin')" :description="__('Biens, services ou travaux nécessaires.')" />
                <button type="button" @click="addLigne()" class="btn btn-secondary btn-sm shrink-0">+ {{ __('Ajouter une ligne') }}</button>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('lignes')" />
            <div class="space-y-3 mt-4">
                <template x-for="(l, i) in lignes" :key="i">
                    <div class="border border-gray-200 rounded-xl p-3 bg-gray-50/50 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-gray-500" x-text="'{{ __('Ligne') }} ' + (i+1)"></span>
                            <button type="button" @click="removeLigne(i)" class="text-red-600 text-xs hover:underline" x-show="lignes.length>1">{{ __('Retirer') }}</button>
                        </div>
                        <div class="grid md:grid-cols-12 gap-2">
                            <div class="md:col-span-4"><label class="form-label">{{ __('Désignation') }}</label><input type="text" :name="`lignes[${i}][designation]`" x-model="l.designation" class="form-input" /></div>
                            <div class="md:col-span-2"><label class="form-label">{{ __('Code article') }}</label><input type="text" :name="`lignes[${i}][code_article]`" x-model="l.code_article" class="form-input" /></div>
                            <div class="md:col-span-3"><label class="form-label">{{ __('Catégorie') }}</label>
                                <select :name="`lignes[${i}][categorie]`" x-model="l.categorie" class="form-select">
                                    <option value="">—</option>
                                    <option value="bien">{{ __('Bien') }}</option>
                                    <option value="service">{{ __('Service') }}</option>
                                    <option value="travaux">{{ __('Travaux') }}</option>
                                    <option value="prestation">{{ __('Prestation intellectuelle') }}</option>
                                </select>
                            </div>
                            <div class="md:col-span-3"><label class="form-label">{{ __('Spécifications') }}</label><input type="text" :name="`lignes[${i}][specifications]`" x-model="l.specifications" class="form-input" /></div>
                            <div class="md:col-span-1"><label class="form-label">{{ __('Unité') }}</label><input type="text" :name="`lignes[${i}][unite]`" x-model="l.unite" class="form-input" /></div>
                            <div class="md:col-span-1"><label class="form-label">{{ __('Qté') }}</label><input type="number" step="0.001" :name="`lignes[${i}][quantite]`" x-model.number="l.quantite" class="form-input" /></div>
                            <div class="md:col-span-2"><label class="form-label">{{ __('Prix unitaire') }}</label><input type="number" step="0.01" :name="`lignes[${i}][prix_unitaire]`" x-model.number="l.prix_unitaire" class="form-input" /></div>
                            <div class="md:col-span-2 text-right"><label class="form-label">{{ __('Montant') }}</label><p class="font-semibold tabular-nums text-ceeac-primary py-2" x-text="fmt(montant(l))"></p></div>
                            <div class="md:col-span-2"><label class="form-label">{{ __('Priorité') }}</label>
                                <select :name="`lignes[${i}][priorite]`" x-model="l.priorite" class="form-select">
                                    <option value="">—</option>
                                    <option value="haute">{{ __('Haute') }}</option>
                                    <option value="moyenne">{{ __('Moyenne') }}</option>
                                    <option value="basse">{{ __('Basse') }}</option>
                                </select>
                            </div>
                            <div class="md:col-span-2"><label class="form-label">{{ __('Échéance') }}</label><input type="date" :name="`lignes[${i}][echeance]`" x-model="l.echeance" class="form-input" /></div>
                            <div class="md:col-span-4"><label class="form-label">{{ __('Ligne budgétaire') }}</label>
                                <select :name="`lignes[${i}][ligne_budgetaire_id]`" x-model="l.ligne_budgetaire_id" class="form-select">
                                    <option value="">—</option>
                                    @foreach ($lignesBudgetaires as $lb)<option value="{{ $lb->id }}">{{ $lb->code }}</option>@endforeach
                                </select>
                            </div>
                            <div class="md:col-span-3"><label class="form-label">{{ __('Source financement') }}</label>
                                <select :name="`lignes[${i}][source_financement_id]`" x-model="l.source_financement_id" class="form-select">
                                    <option value="">—</option>
                                    @foreach ($sourcesFinancement as $sf)<option value="{{ $sf->id }}">{{ $sf->libelle }}</option>@endforeach
                                </select>
                            </div>
                            <div class="md:col-span-2"><label class="form-label">{{ __('Activité PAP') }}</label>
                                <select :name="`lignes[${i}][activite_id]`" x-model="l.activite_id" class="form-select">
                                    <option value="">—</option>
                                    @foreach ($activites as $a)<option value="{{ $a->id }}">{{ $a->code }}</option>@endforeach
                                </select>
                            </div>
                            <div class="md:col-span-3"><label class="form-label">{{ __('Observation') }}</label><input type="text" :name="`lignes[${i}][observation]`" x-model="l.observation" class="form-input" /></div>
                        </div>
                    </div>
                </template>
            </div>
            <div class="flex items-center justify-end mt-3 text-sm font-semibold">
                {{ __('Total estimatif') }} : <span x-text="fmt(total())" class="text-ceeac-primary tabular-nums ml-2"></span>
            </div>
        </x-ds.card>

        {{-- ÉTAPE 4 : Estimation financière (récap) --}}
        <x-ds.card x-show="step===4" x-cloak>
            <x-ds.section-heading :title="__('Estimation financière')" :description="__('Récapitulatif calculé à partir des lignes saisies.')" />
            <div class="grid md:grid-cols-2 gap-6 mt-4">
                <table class="w-full text-sm">
                    <tbody>
                        @foreach (['bien' => __('Biens'), 'service' => __('Services'), 'travaux' => __('Travaux'), 'prestation' => __('Prestations intellectuelles')] as $cle => $lib)
                            <tr class="border-b border-gray-50"><td class="py-2 text-gray-500">{{ $lib }}</td><td class="py-2 text-right font-mono tabular-nums" x-text="fmt(catTotal('{{ $cle }}'))"></td></tr>
                        @endforeach
                        <tr class="border-b border-gray-100"><td class="py-2 font-semibold">{{ __('Sous-total HT') }}</td><td class="py-2 text-right font-mono font-semibold tabular-nums" x-text="fmt(total())"></td></tr>
                        <tr class="border-b border-gray-50"><td class="py-2 text-gray-500">{{ __('Taxes (18%)') }}</td><td class="py-2 text-right font-mono tabular-nums" x-text="fmt(tva())"></td></tr>
                        <tr><td class="py-2 font-bold text-ceeac-primary">{{ __('Montant TTC') }}</td><td class="py-2 text-right font-mono font-bold text-ceeac-primary tabular-nums" x-text="fmt(ttc())"></td></tr>
                    </tbody>
                </table>
                <div class="rounded-xl bg-ceeac-light/40 p-4 flex flex-col justify-center">
                    <div class="text-sm text-gray-500">{{ __('Montant total estimé (TTC)') }}</div>
                    <div class="text-3xl font-bold text-ceeac-primary tabular-nums mt-1"><span x-text="fmt(ttc())"></span> <span class="text-base">XAF</span></div>
                    <div class="text-xs text-gray-500 mt-2"><span x-text="nbLignes()"></span> {{ __('ligne(s) — la disponibilité budgétaire est contrôlée après enregistrement.') }}</div>
                </div>
            </div>
        </x-ds.card>

        {{-- ÉTAPE 5 : Justification --}}
        <x-ds.card x-show="step===5" x-cloak>
            <x-ds.section-heading :title="__('Justification du besoin')" :description="__('Motivation, objectifs et observations.')" />
            <div class="grid md:grid-cols-2 gap-4 mt-4">
                <div class="md:col-span-2">
                    <x-input-label for="justification" :value="__('Justification détaillée')" />
                    <textarea id="justification" name="justification" rows="4" class="form-input mt-1">{{ old('justification', $b?->justification) }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('justification')" />
                    <p class="text-xs text-amber-600 mt-1" x-show="type==='hors_pap'">{{ __('Obligatoire pour un besoin Hors PAP.') }}</p>
                </div>
                <div>
                    <x-input-label for="objectif_recherche" :value="__('Objectif recherché')" />
                    <textarea id="objectif_recherche" name="objectif_recherche" rows="3" class="form-input mt-1">{{ old('objectif_recherche', $b?->objectif_recherche) }}</textarea>
                </div>
                <div>
                    <x-input-label for="consequences_non_realisation" :value="__('Conséquences en cas de non-réalisation')" />
                    <textarea id="consequences_non_realisation" name="consequences_non_realisation" rows="3" class="form-input mt-1">{{ old('consequences_non_realisation', $b?->consequences_non_realisation) }}</textarea>
                </div>
                <div class="md:col-span-2">
                    <x-input-label for="observations" :value="__('Observations')" />
                    <textarea id="observations" name="observations" rows="2" class="form-input mt-1">{{ old('observations', $b?->observations) }}</textarea>
                </div>
            </div>
        </x-ds.card>

        {{-- ÉTAPE 6 : Validation (récap) --}}
        <x-ds.card x-show="step===6" x-cloak>
            <x-ds.section-heading :title="__('Récapitulatif avant enregistrement')" :description="__('Vérifiez les informations puis enregistrez.')" />
            <div class="grid md:grid-cols-3 gap-4 mt-4 text-sm">
                <div><span class="text-gray-500">{{ __('Type') }}</span><p class="font-medium" x-text="type==='pap' ? '{{ __('Besoin PAP') }}' : '{{ __('Besoin Hors PAP') }}'"></p></div>
                <div><span class="text-gray-500">{{ __('Nombre de lignes') }}</span><p class="font-medium" x-text="nbLignes()"></p></div>
                <div><span class="text-gray-500">{{ __('Montant TTC') }}</span><p class="font-bold text-ceeac-primary tabular-nums"><span x-text="fmt(ttc())"></span> XAF</p></div>
            </div>
            <x-ds.alert type="info" class="mt-4">
                {{ __('À l\'enregistrement, le besoin est créé en brouillon. Vous pourrez ensuite déposer des pièces et le soumettre au circuit de validation depuis sa fiche.') }}
            </x-ds.alert>
        </x-ds.card>

        {{-- NAVIGATION --}}
        <div class="flex items-center justify-between">
            <button type="button" @click="prev()" x-show="step>1" class="btn btn-ghost">← {{ __('Précédent') }}</button>
            <span x-show="step===1"></span>
            <div class="flex items-center gap-3">
                <a href="{{ route('expression-besoins.besoins.index') }}" class="btn btn-ghost">{{ __('Annuler') }}</a>
                <button type="button" @click="next()" x-show="step<6" class="btn btn-primary">{{ __('Suivant') }} →</button>
                <button x-show="step===6" x-cloak class="btn btn-primary">{{ $b ? __('Enregistrer les modifications') : __('Enregistrer le besoin') }}</button>
            </div>
        </div>
    </form>

    {{-- PIÈCES (formulaire séparé — édition uniquement, visible à l'étape Justification & pièces) --}}
    <x-ds.card x-show="step===5" x-cloak>
        <x-ds.section-heading :title="__('Justificatifs et pièces')" :description="__('Documents archivés dans la GED.')" />
        @if ($b)
            <form method="POST" action="{{ route('expression-besoins.besoins.pieces.store', $b) }}" enctype="multipart/form-data" class="grid md:grid-cols-3 gap-3 items-end mt-4">
                @csrf
                <div class="md:col-span-2">
                    <x-input-label for="fichier" :value="__('Pièce justificative (déposée dans la GED)')" />
                    <input id="fichier" name="fichier" type="file" required class="block mt-1 w-full text-sm text-gray-700 file:mr-4 file:rounded-lg file:border-0 file:bg-ceeac-light file:px-4 file:py-2 file:text-ceeac-primary file:font-semibold cursor-pointer" />
                </div>
                <div>
                    <x-input-label for="titre" :value="__('Titre (optionnel)')" />
                    <x-text-input id="titre" name="titre" class="block mt-1 w-full" />
                </div>
                <div class="md:col-span-3"><button class="btn btn-secondary btn-sm">{{ __('Déposer la pièce') }}</button></div>
            </form>
            <div class="overflow-x-auto mt-4">
                <table class="data-table">
                    <thead><tr><th>{{ __('Référence GED') }}</th><th>{{ __('Titre') }}</th><th></th></tr></thead>
                    <tbody>
                        @forelse ($documents as $doc)
                            <tr>
                                <td class="font-mono text-xs">{{ $doc->reference }}</td>
                                <td>{{ $doc->titre }}</td>
                                <td class="text-right"><a href="{{ route('ged.documents.show', $doc) }}" class="text-ceeac-bright hover:underline">{{ __('Ouvrir') }}</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center py-4 text-gray-500">{{ __('Aucune pièce.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-sm text-gray-500 mt-4">{{ __('Enregistrez d\'abord le besoin pour pouvoir y déposer des pièces justificatives (archivées automatiquement dans la GED).') }}</p>
        @endif
    </x-ds.card>
</div>
