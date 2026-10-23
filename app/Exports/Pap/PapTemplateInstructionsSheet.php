<?php

namespace App\Exports\Pap;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class PapTemplateInstructionsSheet implements FromArray, ShouldAutoSize, WithTitle
{
    public function title(): string
    {
        return 'Instructions';
    }

    /**
     * @return array<int, array<int, string>>
     */
    public function array(): array
    {
        return [
            ['MODELE OFFICIEL D\'IMPORTATION DU PLAN ANNUEL DE PERFORMANCE (PAP)'],
            ['Version du modele', '1.0'],
            ['Application', 'BUDGET-ENGAGE / Module RBM-GAR'],
            [''],
            ['REGLES DE SAISIE (feuille "Lignes PAP")'],
            ['Colonne', 'Obligatoire', 'Description'],
            ['Code', 'Oui', 'Code unique de la ligne (ex : 201, 2011, 20111, 201111).'],
            ['Libelle', 'Oui', 'Intitule de la ligne.'],
            ['Niveau', 'Oui', 'Une valeur parmi : pilier, axe, produit, sous_produit.'],
            ['Code parent', 'Selon niveau', 'Code de la ligne parente. Vide pour un pilier. Obligatoire sinon.'],
            ['Montant total', 'Oui', 'Montant total en FCFA (entier). Doit etre >= 0.'],
            ['Montant CEEAC', 'Oui', 'Part financee par la CEEAC en FCFA.'],
            ['Montant PTF', 'Oui', 'Part financee par les Partenaires Techniques et Financiers en FCFA.'],
            ['Source', 'Non', 'Reference de la source (defaut : PAP).'],
            [''],
            ['REGLES DE CODIFICATION'],
            ['Pilier', '3 chiffres', 'ex : 201 a 206'],
            ['Axe', '4 chiffres', 'ex : 2011 (parent = pilier 201)'],
            ['Produit', '5 chiffres', 'ex : 20111 (parent = axe 2011)'],
            ['Sous-produit', '6 chiffres', 'ex : 201111 (parent = produit 20111)'],
            [''],
            ['REGLES DE DEPENDANCE ET DE COHERENCE'],
            ['1', 'Le code parent doit exister dans le fichier (ligne de niveau superieur).'],
            ['2', 'Montant CEEAC + Montant PTF doit egaler le Montant total (sinon avertissement).'],
            ['3', 'La somme des montants des lignes enfants doit egaler le montant du parent (sinon avertissement).'],
            ['4', 'Aucun montant negatif n\'est autorise.'],
            ['5', 'Les codes doivent etre uniques ; les doublons sont ignores.'],
            [''],
            ['NOTE', 'Les montants sont saisis directement en FCFA (et non en milliers).'],
        ];
    }
}
