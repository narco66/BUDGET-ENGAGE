# Guide développeur — Design System BUDGET-ENGAGE

## Créer une nouvelle page liste (CRUD)

```blade
<x-app-layout>
    <x-slot name="header">
        <x-ds.page-header
            :title="__('Mon module')"
            :breadcrumb="[['label' => __('Accueil'), 'url' => route('dashboard')], ['label' => __('Mon module')]]"
        >
            <x-slot:actions>
                <x-primary-button type="button" onclick="location.href='{{ route('…create') }}'">
                    {{ __('Créer') }}
                </x-primary-button>
            </x-slot:actions>
        </x-ds.page-header>
    </x-slot>

    <x-ds.page-shell>
        <x-ds.card :padding="false">
            {{-- table data-table --}}
        </x-ds.card>
    </x-ds.page-shell>
</x-app-layout>
```

## Créer un formulaire

- Layout : `<x-ds.page-shell narrow>` ou `max-w-3xl`
- Card unique avec sections
- `<x-input-label>` + `<x-text-input>` + `<x-input-error>`
- Actions : `btn btn-primary` + lien `btn btn-secondary`

## Afficher une alerte métier

```blade
<x-ds.alert type="warning">{{ __('Crédit insuffisant sur la ligne sélectionnée.') }}</x-ds.alert>
```

## Permissions

Ne pas modifier la logique `@can` / policies. Le menu sidebar filtre déjà par permission.

## Fichiers à consulter

- `resources/css/app.css` — classes composants
- `docs/design-system/components.md` — API Blade
- `resources/views/budget/lignes/index.blade.php` — exemple migré

## Build front

```bash
npm run dev    # développement
npm run build  # production
```

## Tests

Après migration visuelle :

```bash
php artisan test --filter=Budget
```

Ne pas casser les assertions métier existantes.
