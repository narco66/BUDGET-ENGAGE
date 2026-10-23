<!DOCTYPE html><html><head><meta charset="utf-8"><title>Synthèse RBM/GAR</title>
<style>body{font-family:DejaVu Sans,sans-serif;font-size:11px}h1{font-size:16px}h2{font-size:13px;margin-top:16px}</style></head><body>
<h1>Synthèse institutionnelle RBM/GAR</h1>
<p>Généré le {{ $generatedAt->format('d/m/Y H:i') }}</p>
@foreach($piliers as $pilier)
    <h2>{{ $pilier->code }} — {{ $pilier->libelle }}</h2>
    <ul>@foreach($pilier->axesStrategiques as $axe)<li>{{ $axe->code }} — {{ $axe->libelle }}</li>@endforeach</ul>
@endforeach
</body></html>