# Guide PDF — Chaîne de la dépense

## Layout partagé

```blade
@extends('pdf.layout')

@section('title', 'Titre du document')

@php
    $pdfReference = $entity->numero;
    $pdfExercice = $entity->exerciceBudgetaire?->annee;
    $pdfGeneratedAt = now()->format('d/m/Y H:i');
@endphp

@section('content')
    <h2 class="pdf-section">Section</h2>
    <table class="pdf-meta">...</table>
    <table class="pdf-data">...</table>
    <div class="pdf-montant">MONTANT : ... FCFA</div>
@endsection
```

## Classes CSS PDF

| Classe | Usage |
|--------|-------|
| `pdf-section` | Titre de section |
| `pdf-meta` | Tableau clé-valeur |
| `pdf-data` | Tableau financier |
| `pdf-right` | Alignement montants |
| `pdf-montant` | Cartouche montant principal |
| `pdf-signatures` | Zone visas |

## QR code (documents certifiés)

```blade
{!! \App\Support\DocumentQrCode::imgTag($payload) !!}
```

## Migration des 22 PDF restants

1. Remplacer le `<!DOCTYPE>` par `@extends('pdf.layout')`
2. Déplacer le CSS inline vers les classes du layout
3. Conserver les données et sections métier
4. Tester génération via route existante
