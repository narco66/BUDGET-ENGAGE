<?php

namespace App\Services\Rbm;

use App\Models\Activite;
use App\Models\AxeStrategique;
use App\Models\Pilier;
use App\Models\Produit;
use App\Models\SousProduit;
use App\Models\Tache;
use Illuminate\Support\Collection;

class RbmTreeService
{
    /**
     * @return Collection<int, Pilier>
     */
    public function fullTree(): Collection
    {
        return Pilier::query()
            ->with([
                'axesStrategiques.produits.sousProduits.activites.taches.indicateurs.typeIndicateur',
                'axesStrategiques.produits.sousProduits.activites.taches.indicateurs.uniteMesure',
            ])
            ->orderBy('ordre')
            ->orderBy('code')
            ->get();
    }

    /**
     * @return array<string, mixed>
     */
    public function matrixData(): array
    {
        $piliers = $this->fullTree();

        $rows = [];
        foreach ($piliers as $pilier) {
            foreach ($pilier->axesStrategiques as $axe) {
                foreach ($axe->produits as $produit) {
                    foreach ($produit->sousProduits as $sousProduit) {
                        foreach ($sousProduit->activites as $activite) {
                            foreach ($activite->taches as $tache) {
                                $rows[] = [
                                    'pilier' => $pilier->libelle,
                                    'axe' => $axe->libelle,
                                    'produit' => $produit->libelle,
                                    'sous_produit' => $sousProduit->libelle,
                                    'activite' => $activite->libelle,
                                    'tache' => $tache->libelle,
                                    'indicateurs' => $tache->indicateurs->pluck('libelle')->join(', '),
                                ];
                            }
                        }
                    }
                }
            }
        }

        return ['rows' => $rows, 'piliers' => $piliers];
    }
}
