<?php

namespace App\Exports\Budget;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class BudgetTemplateInstructionsSheet implements FromArray, ShouldAutoSize, WithTitle
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
            ['MODELE OFFICIEL D\'IMPORTATION DU BUDGET'],
            ['Version du modele', '1.0'],
            ['Application', 'BUDGET-ENGAGE / Module Budget'],
            ['Unite des montants', 'FCFA (saisie directe, sans separateur de milliers)'],
            [''],
            ['REGLES DE SAISIE (feuille "Lignes Budget")'],
            ['Colonne', 'Obligatoire', 'Description'],
            ['Section', 'Oui', 'recette | depense'],
            ['Niveau', 'Oui', 'titre | chapitre | article | paragraphe | ligne'],
            ['Categorie', 'Non', 'fonctionnement, investissement, equipement, personnel, biens_services, transferts, dotations, assises, pap, recette_interne, recette_externe...'],
            ['Code', 'Oui', 'Code unique de la ligne (ex : 66, 661, 6610, 66101).'],
            ['Code parent', 'Selon niveau', 'Code du niveau superieur. Vide pour un titre, obligatoire sinon.'],
            ['Libelle', 'Oui', 'Intitule de la ligne.'],
            ['Montant total', 'Oui', 'Montant total en FCFA (>= 0).'],
            ['Montant CEEAC', 'Oui', 'Part financee par la CEEAC / Etats membres.'],
            ['Montant PTF', 'Oui', 'Part financee par les Partenaires Techniques et Financiers.'],
            ['Source', 'Non', 'Reference de la source (defaut : Budget).'],
            [''],
            ['REGLES DE COHERENCE'],
            ['1', 'Le code parent doit exister dans le fichier et etre d\'un niveau superieur.'],
            ['2', 'Montant CEEAC + Montant PTF doit egaler le Montant total (sinon avertissement).'],
            ['3', 'La somme des montants des lignes enfants doit egaler le montant du parent (sinon avertissement).'],
            ['4', 'Aucun montant negatif n\'est autorise (erreur bloquante).'],
            ['5', 'Equilibre attendu : total des recettes = total des depenses.'],
            ['6', 'La ligne "Depenses liees au PAP" (categorie = pap) est rapprochee du total du PAP importe.'],
        ];
    }
}
