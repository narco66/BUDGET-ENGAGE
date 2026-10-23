<?php

/**
 * Jeu de donnees officiel du Budget 2026 de la CEEAC.
 *
 * Source : « BUDGET EXERCICE 2026 Final-2.pdf » (Annexes 1 et 2 + details).
 * Unite des montants : FCFA (valeurs telles qu'inscrites dans le document).
 *
 * Sections : recette | depense.
 * Niveaux  : titre -> chapitre -> article -> paragraphe -> ligne (parent_code lie au niveau superieur).
 *
 * Reconciliation (entete officielle) :
 *   TOTAL RECETTES = TOTAL DEPENSES = 40 305 795 803
 *   Recettes internes 26 275 514 803 | Recettes externes 14 030 281 000
 *   Depenses : fonctionnement 13 677 514 803 | investissement 25 887 281 000 | equipement 741 000 000
 *   Dont « Depenses liees au PAP » 22 365 281 000 (= total du PAP importe).
 *
 * Les codes en doublon du document (OCR) sont suffixes (ex : 66105b). Les ecarts de sous-totaux
 * presents dans le document source sont conserves et signales en avertissements.
 *
 * @return array<int, array<string, mixed>>
 */

$B = static function (string $section, string $niveau, ?string $categorie, string $code, ?string $parent, string $libelle, $total, $ceeac, $ptf, array $extra = []): array {
    return array_merge([
        'section' => $section,
        'niveau' => $niveau,
        'categorie' => $categorie,
        'code' => $code,
        'parent' => $parent,
        'libelle' => $libelle,
        'total' => $total,
        'ceeac' => $ceeac,
        'ptf' => $ptf,
    ], $extra);
};

return [
    // ============================ RECETTES ============================
    $B('recette', 'titre', 'recette_interne', 'R-INT', null, 'RECETTES INTERNES', 26_275_514_803, 26_275_514_803, 0, [
        'montant_anterieur' => 26_275_486_284, 'montant_realise' => 11_832_586_233, 'taux_realisation' => 45, 'variation' => 0.0,
    ]),
    $B('recette', 'chapitre', 'recette_interne', '7210', 'R-INT', "Contributions de l'exercice", 26_275_514_803, 26_275_514_803, 0),
    $B('recette', 'ligne', 'recette_interne', '72101', '7210', "Republique d'Angola", 2_890_306_628, 2_890_306_628, 0),
    $B('recette', 'ligne', 'recette_interne', '72102', '7210', 'Republique du Burundi', 1_313_775_740, 1_313_775_740, 0),
    $B('recette', 'ligne', 'recette_interne', '72103', '7210', 'Republique du Cameroun', 2_890_306_628, 2_890_306_628, 0),
    $B('recette', 'ligne', 'recette_interne', '72104', '7210', 'Republique Centrafricaine', 1_313_775_740, 1_313_775_740, 0),
    $B('recette', 'ligne', 'recette_interne', '72105', '7210', 'Republique du Congo', 2_890_306_628, 2_890_306_628, 0),
    $B('recette', 'ligne', 'recette_interne', '72106', '7210', 'Republique Democratique du Congo', 2_627_551_480, 2_627_551_480, 0),
    $B('recette', 'ligne', 'recette_interne', '72107', '7210', 'Republique Gabonaise', 2_890_306_628, 2_890_306_628, 0),
    $B('recette', 'ligne', 'recette_interne', '72108', '7210', 'Republique de Guinee Equatoriale', 2_890_306_628, 2_890_306_628, 0),
    $B('recette', 'ligne', 'recette_interne', '72109', '7210', 'Republique Rwandaise', 2_627_551_480, 2_627_551_480, 0),
    $B('recette', 'ligne', 'recette_interne', '72110', '7210', 'Sao Tome et Principe', 1_313_775_740, 1_313_775_740, 0),
    $B('recette', 'ligne', 'recette_interne', '72111', '7210', 'Republique du Tchad', 2_627_551_480, 2_627_551_480, 0),

    $B('recette', 'titre', 'recette_externe', 'R-EXT', null, 'RECETTES EXTERNES', 14_030_281_000, 0, 14_030_281_000, [
        'montant_ptf_anterieur' => 11_846_247_097, 'montant_ptf_realise' => 2_840_427_566, 'taux_realisation' => 24, 'variation' => 18.4,
    ]),
    $B('recette', 'chapitre', 'recette_externe', '741', 'R-EXT', 'Dons des institutions internationales', 14_030_081_000, 0, 14_030_081_000),
    $B('recette', 'article', 'recette_externe', '7411', '741', 'Dons projets', 14_030_081_000, 0, 14_030_081_000),
    $B('recette', 'ligne', 'recette_externe', '7411-BAD', '7411', 'BAD', 9_355_786_000, 0, 9_355_786_000),
    $B('recette', 'ligne', 'recette_externe', '7411-UE', '7411', 'UNION EUROPEENNE (ITC, ONUDI)', 1_280_000_000, 0, 1_280_000_000),
    $B('recette', 'ligne', 'recette_externe', '7411-AU', '7411', 'AU / AUDA-NEPAD / AU-BIRA, Autres IS', 755_000_000, 0, 755_000_000),
    $B('recette', 'ligne', 'recette_externe', '7411-BM', '7411', 'Banque Mondiale', 750_000_000, 0, 750_000_000),
    $B('recette', 'ligne', 'recette_externe', '7411-ONUDI', '7411', 'ONUDI', 715_275_000, 0, 715_275_000),
    $B('recette', 'ligne', 'recette_externe', '7411-ONU', '7411', 'ONU/UNOCA/UNOP/ONUSIDA, ONU-Habitat, Autres IS', 629_020_000, 0, 629_020_000),
    $B('recette', 'ligne', 'recette_externe', '7411-PTF', '7411', 'Consortium PTF', 195_000_000, 0, 195_000_000),
    $B('recette', 'ligne', 'recette_externe', '7411-AGRA', '7411', 'AGRA', 115_000_000, 0, 115_000_000),
    $B('recette', 'ligne', 'recette_externe', '7411-CDC', '7411', 'CDC Afrique', 100_000_000, 0, 100_000_000),
    $B('recette', 'ligne', 'recette_externe', '7411-FAO', '7411', 'FAO', 85_000_000, 0, 85_000_000),
    $B('recette', 'ligne', 'recette_externe', '7411-CARD', '7411', 'CARD', 50_000_000, 0, 50_000_000),

    // ============================ DEPENSES DE FONCTIONNEMENT ============================
    $B('depense', 'titre', 'fonctionnement', 'D-FONC', null, 'DEPENSES DE FONCTIONNEMENT', 13_677_514_803, 13_677_514_803, 0),

    // -- Charges financieres --
    $B('depense', 'chapitre', 'charges_financieres', '67', 'D-FONC', 'CHARGES FINANCIERES', 10_000_000, 10_000_000, 0),
    $B('depense', 'article', 'charges_financieres', '671', '67', 'Interets et frais financiers sur la dette', 10_000_000, 10_000_000, 0),
    $B('depense', 'paragraphe', 'charges_financieres', '6711', '671', 'MICOPAX-RCA (arrieres)', 5_000_000, 5_000_000, 0),
    $B('depense', 'ligne', 'charges_financieres', '67111', '6711', 'MICOPAX-RCA (arrieres)', 5_000_000, 5_000_000, 0),
    $B('depense', 'paragraphe', 'charges_financieres', '6712', '671', 'Frais bancaires', 5_000_000, 5_000_000, 0),
    $B('depense', 'ligne', 'charges_financieres', '67112', '6712', 'Frais bancaires', 5_000_000, 5_000_000, 0),

    // -- Personnel --
    $B('depense', 'chapitre', 'personnel', '66', 'D-FONC', 'DEPENSES DE PERSONNEL', 11_586_264_803, 11_586_264_803, 0),
    $B('depense', 'article', 'personnel', '661', '66', 'Traitement et salaires de base', 6_499_720_906, 6_499_720_906, 0),
    $B('depense', 'paragraphe', 'personnel', '6610', '661', 'Traitement et salaires', 6_499_720_906, 6_499_720_906, 0),
    $B('depense', 'ligne', 'personnel', '66101', '6610', 'Salaire de base des fonctionnaires', 5_377_302_285, 5_377_302_285, 0),
    $B('depense', 'ligne', 'personnel', '66102', '6610', 'Salaire de base des Agents', 619_931_518, 619_931_518, 0),
    $B('depense', 'ligne', 'personnel', '66103', '6610', 'Salaires autres personnels (temporaires)', 352_487_103, 352_487_103, 0),
    $B('depense', 'ligne', 'personnel', '66105', '6610', 'Solde de tout compte', 150_000_000, 150_000_000, 0),
    $B('depense', 'paragraphe', 'personnel', '6611', '661', 'Provision pour recrutement et divers', 0, 0, 0),
    $B('depense', 'ligne', 'personnel', '66104', '6611', 'Recrutement', 0, 0, 0),
    $B('depense', 'ligne', 'personnel', '66105b', '6611', 'Provision pour regularisation echelon', 0, 0, 0),
    $B('depense', 'ligne', 'personnel', '66107', '6611', 'Provision pour nouvelle equipe dirigeante', 0, 0, 0),

    $B('depense', 'article', 'personnel', '663', '66', 'Allocations, indemnites, et avantages divers', 3_513_570_961, 3_513_570_961, 0),
    $B('depense', 'paragraphe', 'personnel', '6631', '663', 'Primes', 46_700_000, 46_700_000, 0),
    $B('depense', 'ligne', 'personnel', '66311', '6631', "Prime d'habillement", 8_400_000, 8_400_000, 0),
    $B('depense', 'ligne', 'personnel', '66312', '6631', 'Prime de recrutement', 10_000_000, 10_000_000, 0),
    $B('depense', 'ligne', 'personnel', '66313', '6631', 'Prime de Caisse', 7_800_000, 7_800_000, 0),
    $B('depense', 'ligne', 'personnel', '66314', '6631', 'Heures Supplementaires', 10_000_000, 10_000_000, 0),
    $B('depense', 'ligne', 'personnel', '66316', '6631', 'Prime de stage', 10_500_000, 10_500_000, 0),
    $B('depense', 'paragraphe', 'personnel', '6632', '663', 'Allocations', 550_810_000, 550_810_000, 0),
    $B('depense', 'ligne', 'personnel', '66321', '6632', 'Allocations familliale', 150_000_000, 150_000_000, 0),
    $B('depense', 'ligne', 'personnel', '66322', '6632', 'Allocations enfant handicape', 1_200_000, 1_200_000, 0),
    $B('depense', 'ligne', 'personnel', '66323', '6632', 'Allocations prenatale', 1_950_000, 1_950_000, 0),
    $B('depense', 'ligne', 'personnel', '66324', '6632', 'Allocations pour conjoints a charge', 220_000_000, 220_000_000, 0),
    $B('depense', 'ligne', 'personnel', '66325', '6632', 'Allocation pour frais de scolarite', 177_660_000, 177_660_000, 0),
    $B('depense', 'paragraphe', 'personnel', '6633', '663', 'Indemnites', 2_916_060_961, 2_916_060_961, 0),
    $B('depense', 'ligne', 'personnel', '66331', '6633', 'Indemnites de fonction', 429_059_136, 429_059_136, 0),
    $B('depense', 'ligne', 'personnel', '66332', '6633', 'Indemnites de sujetion', 204_493_254, 204_493_254, 0),
    $B('depense', 'ligne', 'personnel', '66333', '6633', 'Indemnite de representation', 70_201_440, 70_201_440, 0),
    $B('depense', 'ligne', 'personnel', '66334', '6633', 'Indemnites de logement', 875_100_000, 875_100_000, 0),
    $B('depense', 'ligne', 'personnel', '66335', '6633', 'Indemnites de transport', 376_882_459, 376_882_459, 0),
    $B('depense', 'ligne', 'personnel', '66336', '6633', 'Indemnites interim', 54_559_296, 54_559_296, 0),
    $B('depense', 'ligne', 'personnel', '66337', '6633', "Indemnites d'ajustement de poste", 5_765_376, 5_765_376, 0),
    $B('depense', 'ligne', 'personnel', '66338', '6633', "Indemnite d'eloignement", 200_000_000, 200_000_000, 0),
    $B('depense', 'ligne', 'personnel', '66339', '6633', "Indemnite d'equipement et installation", 700_000_000, 700_000_000, 0),

    $B('depense', 'article', 'personnel', '664', '66', 'Cotisations sociales et Provision Retraite', 894_672_936, 894_672_936, 0),
    $B('depense', 'paragraphe', 'personnel', '6641', '664', 'Provision retraite fonctionnaires', 793_496_808, 793_496_808, 0),
    $B('depense', 'ligne', 'personnel', '66411', '6641', 'Provision retraite fonctionnaire 15% (5% employes et 10% employeur)', 793_496_808, 793_496_808, 0),
    $B('depense', 'paragraphe', 'personnel', '6642', '664', 'Cotisations sociales', 101_176_128, 101_176_128, 0),
    $B('depense', 'ligne', 'personnel', '66421', '6642', 'Prevoyance CNSS', 101_176_128, 101_176_128, 0),
    $B('depense', 'ligne', 'personnel', '66422', '6642', 'Prevoyance CNAMGS', 0, 0, 0),

    $B('depense', 'article', 'personnel', '665', '66', 'Avantage en nature au personnel', 333_300_000, 333_300_000, 0),
    $B('depense', 'paragraphe', 'personnel', '6651', '665', 'Avantage en nature au personnel a mandat', 333_300_000, 333_300_000, 0),
    $B('depense', 'ligne', 'personnel', '66511', '6651', 'Avantage en nature', 333_300_000, 333_300_000, 0),

    $B('depense', 'article', 'personnel', '666', '66', 'Prestations sociales', 345_000_000, 345_000_000, 0),
    $B('depense', 'paragraphe', 'personnel', '6661', '666', 'Prestations sociales', 345_000_000, 345_000_000, 0),
    $B('depense', 'ligne', 'personnel', '66611', '6661', 'Assurances', 320_000_000, 320_000_000, 0),
    $B('depense', 'ligne', 'personnel', '66612', '6661', "Frais d'obseques et d'inhumation", 25_000_000, 25_000_000, 0),

    // -- Biens et services : Achat de biens (60) --
    $B('depense', 'chapitre', 'biens_services', '60', 'D-FONC', 'ACHAT DE BIENS', 200_000_000, 200_000_000, 0),
    $B('depense', 'article', 'biens_services', '601', '60', 'Matiere, materiels et fournitures', 120_000_000, 120_000_000, 0),
    $B('depense', 'paragraphe', 'biens_services', '6011', '601', 'Fournitures', 120_000_000, 120_000_000, 0),
    $B('depense', 'ligne', 'biens_services', '60111', '6011', 'Fourniture de bureau', 50_000_000, 50_000_000, 0),
    $B('depense', 'ligne', 'biens_services', '60112', '6011', 'Consommables informatiques', 50_000_000, 50_000_000, 0),
    $B('depense', 'ligne', 'biens_services', '60113', '6011', 'Carburant et lubrifiant', 20_000_000, 20_000_000, 0),
    $B('depense', 'article', 'biens_services', '605', '60', "Eau, electricite, Gaz et autres sources d'energie", 80_000_000, 80_000_000, 0),
    $B('depense', 'paragraphe', 'biens_services', '6051', '605', 'Eau, electricite, Gaz', 80_000_000, 80_000_000, 0),
    $B('depense', 'ligne', 'biens_services', '60511', '6051', 'Eau et Electricite', 80_000_000, 80_000_000, 0),
    $B('depense', 'ligne', 'biens_services', '60513', '6051', "Gaz et autres sources d'energie", 0, 0, 0),

    // -- Biens et services : Acquisition de services (61) --
    $B('depense', 'chapitre', 'biens_services', '61', 'D-FONC', 'ACQUISITION DE SERVICES', 1_671_250_000, 1_671_250_000, 0),
    $B('depense', 'article', 'biens_services', '611', '61', 'Frais de transports et de missions', 1_070_000_000, 1_070_000_000, 0),
    $B('depense', 'paragraphe', 'biens_services', '6111', '611', 'Frais de transports', 620_000_000, 620_000_000, 0),
    $B('depense', 'ligne', 'biens_services', '61111', '6111', 'Voyages et deplacements', 250_000_000, 250_000_000, 0),
    $B('depense', 'ligne', 'biens_services', '61112', '6111', 'Frais de transports demenagement', 150_000_000, 150_000_000, 0),
    $B('depense', 'ligne', 'biens_services', '61113', '6111', 'Frais de transports conges', 200_000_000, 200_000_000, 0),
    $B('depense', 'ligne', 'biens_services', '61114', '6111', 'Frais de location voitures', 20_000_000, 20_000_000, 0),
    $B('depense', 'paragraphe', 'biens_services', '6112', '611', 'Frais de missions', 450_000_000, 450_000_000, 0),
    $B('depense', 'ligne', 'biens_services', '61121', '6112', 'Frais de mission', 450_000_000, 450_000_000, 0),
    $B('depense', 'article', 'biens_services', '612', '61', 'Loyers et charges locatives', 98_000_000, 98_000_000, 0),
    $B('depense', 'paragraphe', 'biens_services', '6121', '612', 'Loyers', 0, 0, 0),
    $B('depense', 'ligne', 'biens_services', '61211', '6121', 'Habitations', 0, 0, 0),
    $B('depense', 'ligne', 'biens_services', '61212', '6121', 'Immeubles a usage de bureaux', 0, 0, 0),
    $B('depense', 'paragraphe', 'biens_services', '6122', '612', 'Autres charges locatives', 98_000_000, 98_000_000, 0),
    $B('depense', 'ligne', 'biens_services', '61221', '6122', 'Frais de gardiennage', 98_000_000, 98_000_000, 0),
    $B('depense', 'article', 'biens_services', '613', '61', 'Consultant et prestation de services', 209_000_000, 209_000_000, 0),
    $B('depense', 'paragraphe', 'biens_services', '6131', '613', 'Honoraires de prestation de services', 129_000_000, 129_000_000, 0),
    $B('depense', 'ligne', 'biens_services', '61311', '6131', 'Commissaires aux comptes', 20_000_000, 20_000_000, 0),
    $B('depense', 'ligne', 'biens_services', '61312', '6131', 'Honoraires et Etudes et prestations de services', 50_000_000, 50_000_000, 0),
    $B('depense', 'ligne', 'biens_services', '61313', '6131', "Frais d'actes et de contentieux", 4_000_000, 4_000_000, 0),
    $B('depense', 'ligne', 'biens_services', '61314', '6131', 'Frais de recrutement', 55_000_000, 55_000_000, 0),
    $B('depense', 'ligne', 'biens_services', '61315', '6131', 'Frais de formation du personnel', 0, 0, 0),
    $B('depense', 'paragraphe', 'biens_services', '6132', '613', 'Prestations de services', 80_000_000, 80_000_000, 0),
    $B('depense', 'ligne', 'biens_services', '61321', '6132', 'Commissions budgetaires', 50_000_000, 50_000_000, 0),
    $B('depense', 'ligne', 'biens_services', '61322', '6132', 'Comite de tresorerie', 30_000_000, 30_000_000, 0),
    $B('depense', 'ligne', 'biens_services', '61323', '6132', 'Prime de bilan', 0, 0, 0),
    $B('depense', 'article', 'biens_services', '614', '61', 'Entretien, Maintenance et reparation', 154_000_000, 154_000_000, 0),
    $B('depense', 'paragraphe', 'biens_services', '6141', '614', 'Entretien et reparation des biens immobiliers', 100_000_000, 100_000_000, 0),
    $B('depense', 'ligne', 'biens_services', '61411', '6141', 'Frais entretien immeubles siege', 100_000_000, 100_000_000, 0),
    $B('depense', 'ligne', 'biens_services', '61412', '6141', 'Frais entretien residences', 0, 0, 0),
    $B('depense', 'paragraphe', 'biens_services', '6142', '614', 'Entretien et reparation de materiels et mobiliers', 46_500_000, 46_500_000, 0),
    $B('depense', 'ligne', 'biens_services', '61421', '6142', 'Frais entretien materiel', 6_500_000, 6_500_000, 0),
    $B('depense', 'ligne', 'biens_services', '61422', '6142', 'Frais entretien vehicules', 10_000_000, 10_000_000, 0),
    $B('depense', 'ligne', 'biens_services', '61423', '6142', 'Frais entretien climatisation', 0, 0, 0),
    $B('depense', 'ligne', 'biens_services', '61424', '6142', 'Contrats entretien services annexes', 30_000_000, 30_000_000, 0),
    $B('depense', 'paragraphe', 'biens_services', '6143', '614', 'Maintenance et reparation', 7_500_000, 7_500_000, 0),
    $B('depense', 'ligne', 'biens_services', '61431', '6143', 'Maintenance informatique', 5_000_000, 5_000_000, 0),
    $B('depense', 'ligne', 'biens_services', '61432', '6143', 'Reparation vehicules', 2_500_000, 2_500_000, 0),
    $B('depense', 'ligne', 'biens_services', '61433', '6143', 'Reparation materiel', 0, 0, 0),
    $B('depense', 'ligne', 'biens_services', '61434', '6143', 'Reparation mobilier', 0, 0, 0),
    $B('depense', 'article', 'biens_services', '615', '61', 'Assurances Multirisques', 6_000_000, 6_000_000, 0),
    $B('depense', 'paragraphe', 'biens_services', '6151', '615', 'Assurance materiel roulant et immobilier', 6_000_000, 6_000_000, 0),
    $B('depense', 'ligne', 'biens_services', '61511', '6151', 'Assurance vehicules', 1_000_000, 1_000_000, 0),
    $B('depense', 'ligne', 'biens_services', '61512', '6151', 'Assurance batiments', 5_000_000, 5_000_000, 0),
    $B('depense', 'article', 'biens_services', '617', '61', 'Frais de relations publiques et communication', 22_750_000, 22_750_000, 0),
    $B('depense', 'paragraphe', 'biens_services', '6171', '617', 'Frais de relations publiques', 22_750_000, 22_750_000, 0),
    $B('depense', 'ligne', 'biens_services', '61711', '6171', 'Frais de reception et de representation', 10_000_000, 10_000_000, 0),
    $B('depense', 'ligne', 'biens_services', '61712', '6171', 'Annonces, insertions et publications', 2_000_000, 2_000_000, 0),
    $B('depense', 'ligne', 'biens_services', '61713', '6171', 'Catalogues, imprimes publicitaires et traductions', 750_000, 750_000, 0),
    $B('depense', 'ligne', 'biens_services', '61714', '6171', 'Frais de seminaires et ateliers', 5_000_000, 5_000_000, 0),
    $B('depense', 'ligne', 'biens_services', '61715', '6171', 'Fetes et ceremonies', 5_000_000, 5_000_000, 0),
    $B('depense', 'ligne', 'biens_services', '61716', '6171', 'Frais de documentation et de bibliotheque', 0, 0, 0),
    $B('depense', 'article', 'biens_services', '618', '61', 'Depenses de Communication', 111_500_000, 111_500_000, 0),
    $B('depense', 'paragraphe', 'biens_services', '6181', '618', 'Frais de telephone et de telecopie', 111_500_000, 111_500_000, 0),
    $B('depense', 'ligne', 'biens_services', '61811', '6181', 'Frais de telephone', 1_000_000, 1_000_000, 0),
    $B('depense', 'ligne', 'biens_services', '61812', '6181', 'Frais de connexion internet et site web', 60_000_000, 60_000_000, 0),
    $B('depense', 'ligne', 'biens_services', '61813', '6181', 'Frais de Poste et telecommunication', 500_000, 500_000, 0),
    $B('depense', 'ligne', 'biens_services', '61814', '6181', 'Frais de courrier express', 25_000_000, 25_000_000, 0),
    $B('depense', 'ligne', 'biens_services', '61815', '6181', 'Frais edition et publication JO et Communication de Presse', 25_000_000, 25_000_000, 0),

    // -- Transferts de fonctionnement (64) --
    $B('depense', 'chapitre', 'transferts', '64', 'D-FONC', 'DEPENSES DE TRANSFERTS DE FONCTIONNEMENT', 210_000_000, 210_000_000, 0),
    $B('depense', 'article', 'transferts', '641', '64', 'Organes et Institutions de la Communaute', 0, 0, 0),
    $B('depense', 'paragraphe', 'transferts', '6411', '641', 'Organes de la Communaute', 0, 0, 0),
    $B('depense', 'ligne', 'transferts', '64111', '6411', 'Parlement de la Communaute', 0, 0, 0),
    $B('depense', 'ligne', 'transferts', '64112', '6411', 'Cour de Justice de la Communaute', 0, 0, 0),
    $B('depense', 'ligne', 'transferts', '64113', '6411', 'Cour des comptes de la Communaute', 0, 0, 0),
    $B('depense', 'article', 'transferts', '644', '64', 'Representations et Bureaux de liaison', 210_000_000, 210_000_000, 0),
    $B('depense', 'paragraphe', 'transferts', '6441', '644', 'Bureaux de liaison - Pays', 210_000_000, 210_000_000, 0),
    $B('depense', 'ligne', 'transferts', '64411', '6441', 'Bureau liaison RCA', 30_000_000, 30_000_000, 0),
    $B('depense', 'ligne', 'transferts', '64412', '6441', 'Bureau liaison RDC', 30_000_000, 30_000_000, 0),
    $B('depense', 'ligne', 'transferts', '64413', '6441', "Bureau liaison aupres de l'UA", 30_000_000, 30_000_000, 0),
    $B('depense', 'ligne', 'transferts', '64414', '6441', 'Bureau liaison BURUNDI / CIRGL', 30_000_000, 30_000_000, 0),
    $B('depense', 'ligne', 'transferts', '64415', '6441', 'Bureau liaison CONGO', 30_000_000, 30_000_000, 0),
    $B('depense', 'ligne', 'transferts', '64416', '6441', 'Bureau liaison TCHAD', 30_000_000, 30_000_000, 0),
    $B('depense', 'ligne', 'transferts', '64417', '6441', 'Bureau liaison GUINEE EQUATORIALE', 30_000_000, 30_000_000, 0),
    $B('depense', 'ligne', 'transferts', '64418', '6441', 'Bureau liaison RWANDA', 0, 0, 0),

    // ============================ DEPENSES D'INVESTISSEMENT ============================
    $B('depense', 'titre', 'investissement', 'D-INV', null, "DEPENSES D'INVESTISSEMENT", 25_887_281_000, 11_857_000_000, 14_030_281_000),

    // -- Remboursement du capital de la dette (15) : 0 en 2026 --
    $B('depense', 'chapitre', 'dette', '15', 'D-INV', 'Remboursement du capital de la dette', 0, 0, 0),
    $B('depense', 'ligne', 'dette', '15011', '15', 'MICOPAX', 0, 0, 0),
    $B('depense', 'ligne', 'dette', '15012', '15', 'PACEBCO', 0, 0, 0),
    $B('depense', 'ligne', 'dette', '15013', '15', 'PAPS II', 0, 0, 0),
    $B('depense', 'ligne', 'dette', '15014', '15', 'Dettes sociales', 0, 0, 0),
    $B('depense', 'ligne', 'dette', '15015', '15', 'Dettes envers les tiers', 0, 0, 0),

    // -- Depenses liees au PAP (rapprochement avec le PAP importe) --
    $B('depense', 'chapitre', 'pap', 'D-PAP', 'D-INV', 'Depenses liees au Plan annuel de performance - Programmes et projets des PTF', 22_365_281_000, 8_335_000_000, 14_030_281_000),

    // -- Assises statutaires et autres depenses des programmes (209) --
    $B('depense', 'chapitre', 'assises', '209', 'D-INV', 'Autres depenses des programmes (Assises statutaires)', 1_525_000_000, 1_525_000_000, 0),
    $B('depense', 'article', 'assises', '2091', '209', 'Assises statutaires', 825_000_000, 825_000_000, 0),
    $B('depense', 'ligne', 'assises', '20911', '2091', "Conference des Chefs d'Etats et de Gouvernement", 400_000_000, 400_000_000, 0),
    $B('depense', 'ligne', 'assises', '20912', '2091', 'Conseil des Ministres', 200_000_000, 200_000_000, 0),
    $B('depense', 'ligne', 'assises', '20913', '2091', 'Comite des Representants Permanents (COREP)', 25_000_000, 25_000_000, 0),
    $B('depense', 'ligne', 'assises', '20914', '2091', 'Comites Inter Etats', 50_000_000, 50_000_000, 0),
    $B('depense', 'ligne', 'assises', '20915', '2091', 'Comite Technique Specialise', 150_000_000, 150_000_000, 0),
    $B('depense', 'article', 'assises', '2092', '209', 'Autres depenses des programmes', 200_000_000, 200_000_000, 0),
    $B('depense', 'ligne', 'assises', '20921', '2092', 'Autres programmes CEEAC', 200_000_000, 200_000_000, 0),
    $B('depense', 'article', 'assises', '2093', '209', 'Mise en oeuvre des recommandations des assises statutaires et internationales', 500_000_000, 500_000_000, 0),
    $B('depense', 'ligne', 'assises', '20931', '2093', 'Mission circulaire de haut niveau sur le financement durable de la CEEAC', 300_000_000, 300_000_000, 0),
    $B('depense', 'ligne', 'assises', '20932', '2093', 'Missions et autres activites de mediation pour la gestion des crises securitaires', 200_000_000, 200_000_000, 0),

    // -- Dotations speciales aux institutions specialisees (63) --
    $B('depense', 'chapitre', 'dotations', '63', 'D-INV', 'DOTATIONS SPECIALES', 1_997_000_000, 1_997_000_000, 0),
    $B('depense', 'article', 'dotations', '631', '63', 'SUBVENTIONS VERSEES', 1_847_000_000, 1_847_000_000, 0),
    $B('depense', 'paragraphe', 'dotations', '6311', '631', 'Dotations speciales', 1_847_000_000, 1_847_000_000, 0),
    $B('depense', 'ligne', 'dotations', '63111', '6311', "Pool Energetique pour l'Afrique Centrale (PEAC)", 25_000_000, 25_000_000, 0),
    $B('depense', 'ligne', 'dotations', '63112', '6311', "Commission des Forets de l'AC (COMIFAC)", 0, 0, 0),
    $B('depense', 'ligne', 'dotations', '63113', '6311', 'Commission Regionale des Peches du Golfe de Guinee (COREP)', 25_000_000, 25_000_000, 0),
    $B('depense', 'ligne', 'dotations', '63114', '6311', "Centre d'Application des Previsions climatologiques d'Afrique Centrale (CAPC-AC)", 25_000_000, 25_000_000, 0),
    $B('depense', 'ligne', 'dotations', '63115', '6311', 'Centre International de Coordination (CIC)', 636_000_000, 636_000_000, 0),
    $B('depense', 'ligne', 'dotations', '63116', '6311', 'Centre Multinational de Coordination-Zone D', 50_000_000, 50_000_000, 0),
    $B('depense', 'ligne', 'dotations', '63117', '6311', "Centre Regional de la Securite Maritime de l'Afrique Centrale (CRESMAC)", 700_000_000, 700_000_000, 0),
    $B('depense', 'ligne', 'dotations', '63118', '6311', 'Colleges des Hautes Etudes des Strategie et de Defense (CHESD)', 0, 0, 0),
    $B('depense', 'ligne', 'dotations', '63119', '6311', "Centre pour Les Energies Renouvelables et Efficacite Energetique de l'Afrique Centrale (CEREE-AC)", 361_000_000, 361_000_000, 0),
    $B('depense', 'ligne', 'dotations', '63120', '6311', 'Organisation pour la Conservation de la Faune Sauvage en Afrique (OCFSA)', 0, 0, 0),
    $B('depense', 'ligne', 'dotations', '63121', '6311', "Forum International sur les Peuples Autochtones d'Afrique Centrale (FIPAC)", 0, 0, 0),
    $B('depense', 'ligne', 'dotations', '63122', '6311', "Conference des Ecosystemes Forestiers Denses et Humides d'Afrique Centrale", 0, 0, 0),
    $B('depense', 'ligne', 'dotations', '63123', '6311', 'Centre Multinational de Coordination-Zone A', 25_000_000, 25_000_000, 0),
    $B('depense', 'article', 'dotations', '639', '63', 'AUTRES SUBVENTIONS', 150_000_000, 150_000_000, 0),
    $B('depense', 'paragraphe', 'dotations', '6391', '639', 'Autres categories de beneficiaires', 150_000_000, 150_000_000, 0),
    $B('depense', 'ligne', 'dotations', '63911', '6391', 'COPILCER-AC', 150_000_000, 150_000_000, 0),

    // ============================ DEPENSES D'EQUIPEMENT ============================
    $B('depense', 'titre', 'equipement', 'D-EQUI', null, "DEPENSES D'EQUIPEMENT", 741_000_000, 741_000_000, 0),

    // -- Equipements informatiques (213) --
    $B('depense', 'chapitre', 'equipement', '213', 'D-EQUI', 'Equipements informatiques', 391_000_000, 391_000_000, 0),
    $B('depense', 'article', 'equipement', '2132', '213', 'Equipements informatiques', 300_000_000, 300_000_000, 0),
    $B('depense', 'ligne', 'equipement', '21321', '2132', 'Renouvellement equipement informatique et licences', 100_000_000, 100_000_000, 0),
    $B('depense', 'ligne', 'equipement', '21322', '2132', 'Acquisition des equipements locaux techniques (serveurs et equipements de protection)', 0, 0, 0),
    $B('depense', 'ligne', 'equipement', '21323', '2132', "Modernisation et securisation de l'infrastructure systeme et reseau de la Commission", 100_000_000, 100_000_000, 0),
    $B('depense', 'ligne', 'equipement', '21324', '2132', 'Acquisition ERP', 100_000_000, 100_000_000, 0),
    $B('depense', 'ligne', 'equipement', '21325', '2132', 'Acquisition des autres sectoriels', 0, 0, 0),
    $B('depense', 'article', 'equipement', '2133', '213', "Systeme d'organisation", 91_000_000, 91_000_000, 0),
    $B('depense', 'ligne', 'equipement', '21331', '2133', 'Maintenance Evolutive du Site web', 10_000_000, 10_000_000, 0),
    $B('depense', 'ligne', 'equipement', '21332', '2133', 'Solution intranet', 5_000_000, 5_000_000, 0),
    $B('depense', 'ligne', 'equipement', '21333', '2133', 'Systeme gestion documentaire et archivage electronique', 50_000_000, 50_000_000, 0),
    $B('depense', 'ligne', 'equipement', '21334', '2133', 'Mise en place du ou des outils de type ESB (Entreprise Service Bus)', 10_000_000, 10_000_000, 0),
    $B('depense', 'ligne', 'equipement', '21335', '2133', 'Mise en place CEEAC Web TV', 1_000_000, 1_000_000, 0),
    $B('depense', 'ligne', 'equipement', '21336', '2133', 'Acquisition des equipements de production audio visuelle', 5_000_000, 5_000_000, 0),
    $B('depense', 'ligne', 'equipement', '21337', '2133', "Acquisition d'unite de production et impression numerique", 10_000_000, 10_000_000, 0),

    // -- Autres depenses a caractere d'immobilisation (23) --
    $B('depense', 'chapitre', 'equipement', '23', 'D-EQUI', "Autres depenses a caractere d'immobilisation", 20_000_000, 20_000_000, 0),
    $B('depense', 'article', 'equipement', '231', '23', 'Batiments administratifs a usage de bureau', 20_000_000, 20_000_000, 0),
    $B('depense', 'paragraphe', 'equipement', '2311', '231', 'Batiments administratifs', 10_000_000, 10_000_000, 0),
    $B('depense', 'ligne', 'equipement', '23111', '2311', "Refection, Maintenance et travaux d'entretien batiments siege", 0, 0, 0),
    $B('depense', 'ligne', 'equipement', '23112', '2311', "Refection, Maintenance et travaux d'entretien batiments Annexes", 0, 0, 0),
    $B('depense', 'ligne', 'equipement', '23113', '2311', 'Reparation Plomberie, Electricite', 0, 0, 0),
    $B('depense', 'ligne', 'equipement', '23114', '2311', 'Equipements securite incendie', 10_000_000, 10_000_000, 0),
    $B('depense', 'ligne', 'equipement', '23115', '2311', 'Amenagements site du nouveau Siege', 0, 0, 0),
    $B('depense', 'ligne', 'equipement', '23116', '2311', 'Amenagement anciens batiments', 0, 0, 0),
    $B('depense', 'article', 'equipement', '236', '23', "Reseaux informatiques et systeme d'organisation", 10_000_000, 10_000_000, 0),
    $B('depense', 'paragraphe', 'equipement', '2361', '236', 'Reseaux informatiques', 10_000_000, 10_000_000, 0),
    $B('depense', 'ligne', 'equipement', '23611', '2361', 'Travaux de cablage informatique et reseau electrique ondule', 0, 0, 0),
    $B('depense', 'ligne', 'equipement', '23612', '2361', 'Amenagement de la salle des serveurs NTIC', 10_000_000, 10_000_000, 0),
    $B('depense', 'ligne', 'equipement', '23613', '2361', 'Telephonie IP et interconnection des reseaux', 0, 0, 0),

    // -- Acquisition du materiel et du mobilier de service (24) --
    $B('depense', 'chapitre', 'equipement', '24', 'D-EQUI', 'Acquisition du materiel et du mobilier de service', 330_000_000, 330_000_000, 0),
    $B('depense', 'article', 'equipement', '241', '24', 'Acquisition mobilier de bureau', 110_000_000, 110_000_000, 0),
    $B('depense', 'paragraphe', 'equipement', '2411', '241', 'Mobilier de bureau', 105_000_000, 105_000_000, 0),
    $B('depense', 'ligne', 'equipement', '24111', '2411', 'Tables bureaux avec tiroirs', 50_000_000, 50_000_000, 0),
    $B('depense', 'ligne', 'equipement', '24112', '2411', 'Achat chaises & Fauteuils batiment du Siege', 25_000_000, 25_000_000, 0),
    $B('depense', 'ligne', 'equipement', '24113', '2411', 'Achat Chaises & fauteuils Batiments annexes', 15_000_000, 15_000_000, 0),
    $B('depense', 'ligne', 'equipement', '24114', '2411', 'Armoires de rangement batiment du siege', 10_000_000, 10_000_000, 0),
    $B('depense', 'ligne', 'equipement', '24115', '2411', 'Armoires de rangement batiments annexes', 5_000_000, 5_000_000, 0),
    $B('depense', 'paragraphe', 'equipement', '2412', '241', 'Materiel de bureau', 5_000_000, 5_000_000, 0),
    $B('depense', 'ligne', 'equipement', '24121', '2412', 'Petit materiel de bureau', 5_000_000, 5_000_000, 0),
    $B('depense', 'article', 'equipement', '242', '24', 'Acquisition de materiel informatique de Bureau', 20_000_000, 20_000_000, 0),
    $B('depense', 'paragraphe', 'equipement', '2421', '242', 'Materiel informatique de bureau', 20_000_000, 20_000_000, 0),
    $B('depense', 'ligne', 'equipement', '24211', '2421', "Kit d'acces et videosurveillance", 1_000_000, 1_000_000, 0),
    $B('depense', 'ligne', 'equipement', '24212', '2421', 'Dispositif machine pointeur', 0, 0, 0),
    $B('depense', 'ligne', 'equipement', '24213', '2421', 'Petits equipements informatiques', 10_000_000, 10_000_000, 0),
    $B('depense', 'ligne', 'equipement', '24214', '2421', 'Multiprises et cablages informatiques', 5_000_000, 5_000_000, 0),
    $B('depense', 'ligne', 'equipement', '24215', '2421', 'Petits materiels informatiques', 4_000_000, 4_000_000, 0),
    $B('depense', 'article', 'equipement', '243', '24', 'Acquisition du materiel de transport', 200_000_000, 200_000_000, 0),
    $B('depense', 'paragraphe', 'equipement', '2431', '243', 'Materiel de transport', 200_000_000, 200_000_000, 0),
    $B('depense', 'ligne', 'equipement', '24311', '2431', 'Materiel de transport', 200_000_000, 200_000_000, 0),
    $B('depense', 'article', 'equipement', '244', '24', 'Acquisition materiel et outillage technique', 0, 0, 0),
    $B('depense', 'paragraphe', 'equipement', '2441', '244', 'Materiel et outillage technique', 0, 0, 0),
    $B('depense', 'ligne', 'equipement', '24411', '2441', 'Menusierie, Peinture, Plomberie', 0, 0, 0),
];
