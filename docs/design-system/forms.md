# Formulaires — Design System BUDGET-ENGAGE

## Champs standards

```blade
<div class="form-group">
    <x-input-label for="montant" :value="__('Montant (FCFA)')" />
    <x-text-input id="montant" name="montant" type="number" step="0.01" class="w-full" />
    <x-input-error :messages="$errors->get('montant')" class="mt-1" />
</div>
```

- Label : `.form-label` via `<x-input-label>`
- Input : `.form-input` via `<x-text-input>`
- Select : classe `.form-select` sur `<select>`

## Champs obligatoires

Indiquer `(obligatoire)` dans le label ou `required` + aria.

## Validation

- Messages d'erreur sous le champ (`<x-input-error>`)
- Bordure rouge automatique Laravel + `@tailwindcss/forms`
- Ne pas bloquer uniquement par couleur

## Formulaires longs

- Regrouper en `<x-ds.card title="Section">`
- Barre d'actions fixe en bas : `btn btn-primary` + `btn btn-secondary`

## Montants financiers

- Type `number`, `step="0.01"`, `min="0"`
- Affichage : `<x-ds.money>`
- Saisie : séparateur décimal `.` côté serveur

## Upload

Conserver le pattern existant (`enctype="multipart/form-data"`) dans une carte dédiée.

## Auth

Layout `guest` : carte blanche `rounded-2xl shadow-hover` — ne pas modifier la structure.

## Protection double soumission

```html
<button type="submit" class="btn btn-primary" @click="$el.disabled=true; $el.form.submit()">
```

Ou désactivation côté serveur via token CSRF (déjà en place).
