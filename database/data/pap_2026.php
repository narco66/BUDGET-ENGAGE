<?php

/**
 * Jeu de donnees officiel du Plan Annuel de Performance (PAP) 2026 de la CEEAC.
 *
 * Source : « BUDGET EXERCICE 2026 Final-2.pdf » (Plan Annuel de Performance 2026).
 * Unite des montants : MILLIERS de FCFA tels qu'inscrits dans le document.
 *   -> Le service d'import multiplie ces valeurs par 1000 pour obtenir des FCFA.
 *
 * Niveaux : pilier (code 3 chiffres) -> axe (4 chiffres) -> produit (5 chiffres)
 *           -> sous_produit (6 chiffres, importable via le modele Excel/CSV).
 *
 * Les totaux Pilier et Axe se reconcilient exactement avec l'entete du document :
 *   TOTAL 22 365 281 000 | CEEAC 8 335 000 000 | PTF 14 030 281 000 (en FCFA).
 *
 * Certaines scommes de produits ne se reconcilient pas a leur axe parent : il s'agit
 * d'incoherences presentes dans le document source. Elles sont conservees telles quelles
 * et signalees automatiquement (avertissements) par le controle de coherence des sous-totaux.
 *
 * @return array<int, array{code:string, libelle:string, niveau:string, parent:?string, total:int|float, ceeac:int|float, ptf:int|float}>
 */

$L = static fn (string $code, string $libelle, string $niveau, ?string $parent, $total, $ceeac, $ptf): array => [
    'code' => $code,
    'libelle' => $libelle,
    'niveau' => $niveau,
    'parent' => $parent,
    'total' => $total,
    'ceeac' => $ceeac,
    'ptf' => $ptf,
];

return [
    // ============================ PILIER 1 ============================
    $L('201', 'PILIER 1 - INTEGRATION POLITIQUE, PAIX ET SECURITE', 'pilier', null, 1_750_000, 1_750_000, 0),
    $L('2011', 'AXE 1 : RENFORCER LA GOUVERNANCE POLITIQUE', 'axe', '201', 655_000, 655_000, 0),
    $L('20111', 'Fonctionnement du Comite des Sages', 'produit', '2011', 150_000, 150_000, 0),
    $L('20112', 'Mise en place et operationalisation des mecanismes communautaires de mediation et de concertation politique entre les Etats membres', 'produit', '2011', 255_000, 255_000, 0),
    $L('20113', 'Mise en place et operationalisation des mecanismes communautaires de promotion de la bonne gouvernance et de la lutte contre la corruption dans les Etats membres', 'produit', '2011', 50_000, 50_000, 0),
    $L('20114', "Mise en place et operationnalisation des mecanismes communautaires de promotion de l'etat de droit, des droits de l'homme, de la democratie et des elections dans les Etats membres", 'produit', '2011', 200_000, 200_000, 0),
    $L('20115', "Mise en place et operationnalisation des mecanismes communautaires de promotion de la migration, du droit d'etablissement et de la libre circulation entre les Etats membres", 'produit', '2011', 0, 0, 0),
    $L('20116', "Mise en place et operationalisation des mecanismes communautaires de lutte contre les discours de haine et d'incitation a la violence dans les Etats membres", 'produit', '2011', 0, 0, 0),

    $L('2012', 'AXE 2 : MAINTENIR, RETABLIR ET CONSOLIDER LA PAIX EN AFRIQUE CENTRALE', 'axe', '201', 885_000, 885_000, 0),
    $L('20121', 'Renforcement des capacites de la FOMAC', 'produit', '2012', 390_000, 390_000, 0),
    $L('20122', 'Mise en oeuvre de la Strategie regionale de prevention et de lutte contre le terrorisme en Afrique Centrale', 'produit', '2012', 70_000, 70_000, 0),
    $L('20123', 'Operationnalisation de la Gouvernance integree des frontieres en Afrique Centrale', 'produit', '2012', 120_000, 120_000, 0),
    $L('20124', 'Delimitation et demarcation des frontieres', 'produit', '2012', 75_000, 75_000, 0),
    $L('20125', 'Mise en oeuvre de la Politique integree des Mers et des eaux continentales de la CEEAC', 'produit', '2012', 230_000, 230_000, 0),

    $L('2013', 'AXE 3 : PREVENIR LES CONFLITS ET CRISES EN AFRIQUE CENTRALE', 'axe', '201', 210_000, 210_000, 0),
    $L('20131', "Renforcement du dispositif operationnel du Mecanisme d'Alerte Rapide (MARAC)", 'produit', '2013', 100_000, 100_000, 0),
    $L('20132', 'Mise en place et Operationnalisation des mecanismes fonctionnels du MARAC', 'produit', '2013', 110_000, 110_000, 0),

    // ============================ PILIER 2 ============================
    $L('202', 'PILIER 2 - INTEGRATION ECONOMIQUE ET COMMERCIALE', 'pilier', null, 3_095_000, 1_750_000, 1_345_000),
    $L('2021', 'AXE 1 : OPERATIONNALISER LE MARCHE COMMUN REGIONAL', 'axe', '202', 890_000, 625_000, 265_000),
    $L('20211', "Operationnalisation de la zone de libre-echange et de l'union douaniere", 'produit', '2021', 255_000, 255_000, 0),
    $L('20212', 'Elimination des BNT et Facilitation des echanges', 'produit', '2021', 200_000, 200_000, 0),
    $L('20213', 'Harmonisation des politiques commerciales sectorielles', 'produit', '2021', 335_000, 70_000, 265_000),
    $L('20214', 'Insertion harmonieuse des Etats membres de la CEEAC dans le commerce continental et mondial', 'produit', '2021', 50_000, 50_000, 0),
    $L('20215', "Operationnalisation de la libre-circulation des personnes et le droit d'etablissement au sein de la CEEAC", 'produit', '2021', 50_000, 50_000, 0),

    $L('2022', "AXE 2 : AMELIORER LA GOUVERNANCE ECONOMIQUE ET L'ENVIRONNEMENT DES AFFAIRES", 'axe', '202', 275_000, 275_000, 0),
    $L('20221', "Soutien aux reformes pour l'amelioration de l'environnement des affaires", 'produit', '2022', 160_000, 160_000, 0),
    $L('20222', "Appui a l'amelioration de la gouvernance economique", 'produit', '2022', 115_000, 115_000, 0),
    $L('20223', 'Operationnalisation des cadres et instruments de cooperation economique, financiere et monetaire de la Communaute', 'produit', '2022', 95_000, 95_000, 0),

    $L('2023', "AXE 3 : RENFORCER LES CAPACITES PRODUCTIVES ET D'INNOVATION ET L'ECOSYSTEME DE DEVELOPPEMENT DES ENTREPRISES", 'axe', '202', 1_490_000, 510_000, 980_000),
    $L('20231', 'Soutien au developpement de nouveaux leviers generateurs de la valeur ajoutee et de la diversification', 'produit', '2023', 300_000, 0, 300_000),
    $L('20232', 'Renforcement des capacites technologiques et techniques de la region', 'produit', '2023', 305_000, 75_000, 230_000),
    $L('20233', "Renforcement de l'infrastructure Qualite de la region", 'produit', '2023', 475_000, 75_000, 400_000),
    $L('20234', 'Promotion de la propriete intellectuelle', 'produit', '2023', 50_000, 50_000, 0),
    $L('20235', "Renforcement de la cooperation avec les Institutions d'appui et les organisations d'acteurs du secteur prive", 'produit', '2023', 360_000, 310_000, 50_000),

    $L('2024', 'AXE 4 : OPERATIONALISER LE SYSTEME STATISTIQUE DE LA COMMUNAUTE', 'axe', '202', 440_000, 340_000, 100_000),
    $L('20241', 'Gouvernance et coordination de la statistique regionale', 'produit', '2024', 195_000, 95_000, 100_000),
    $L('20242', 'Production des statistiques et des previsions economiques', 'produit', '2024', 70_000, 70_000, 0),
    $L('20243', 'Operationnalisation des mecanismes de publication, diffusion et valorisation des statistiques', 'produit', '2024', 40_000, 40_000, 0),
    $L('20244', "Suivi de l'integration regionale", 'produit', '2024', 135_000, 135_000, 0),

    // ============================ PILIER 3 ============================
    $L('203', 'PILIER 3 - AMENAGEMENT DU TERRITOIRE, DEVELOPPEMENT DES INFRASTRUCTURES ET INTEGRATION PHYSIQUE', 'pilier', null, 11_172_061, 1_000_000, 10_172_061),
    $L('2031', 'AXE 1 : AMENAGER LE TERRITOIRE COMMUNAUTAIRE ET DEVELOPPER DES INFRASTRUCTURES DE TRANSPORT DE QUALITE ET ACCESSIBLES', 'axe', '203', 6_928_536, 290_000, 6_638_536),
    $L('20311', 'Harmonisation des strategies nationales de developpement des infrastructures routieres terrestres', 'produit', '2031', 50_000, 50_000, 0),
    $L('20312', 'Renforcement des infrastructures transnationales de transport (routier, ferroviaire et fluvial)', 'produit', '2031', 4_236_332, 205_000, 4_031_332),
    $L('20313', 'Renforcement de la connectivite des voies aeriennes et maritimes', 'produit', '2031', 2_642_204, 35_000, 2_607_204),

    $L('2032', "AXE 2 : ACCELERER LA MISE EN PLACE D'UN MARCHE REGIONAL DE L'ENERGIE", 'axe', '203', 3_142_525, 500_000, 2_642_525),
    $L('20321', "Renforcement et consolidation de la Gouvernance regionale du secteur de l'energie en Afrique Centrale", 'produit', '2032', 2_862_525, 220_000, 2_642_525),
    $L('20322', "Interconnexion regionale, electrification transfrontaliere et acces equitable a l'energie dans les zones rurales, zones isolees", 'produit', '2032', 35_000, 35_000, 0),
    $L('20323', "Mise en place d'un dispositif integre de financement, de coordination regionale ainsi que de suivi et d'evaluation des projets energetiques", 'produit', '2032', 245_000, 245_000, 0),

    $L('2033', "AXE 3 : ACCELERER L'INTEGRATION REGIONALE NUMERIQUE", 'axe', '203', 1_101_000, 210_000, 891_000),
    $L('20331', 'Harmonisation des Politiques, strategies et des reglementations nationales', 'produit', '2033', 861_000, 125_000, 736_000),
    $L('20332', "Developpement des infrastructures sous-tendant l'economie numerique", 'produit', '2033', 180_000, 25_000, 155_000),
    $L('20333', "Appui a l'Interconnexion transfrontaliere et l'inclusion numerique", 'produit', '2033', 60_000, 60_000, 0),
    $L('20334', 'Renforcement du partenariat regional et international', 'produit', '2033', 25_000, 25_000, 0),

    // ============================ PILIER 4 ============================
    $L('204', 'PILIER 4 - INTEGRATION ENVIRONNEMENTALE, AGRICULTURE, TRANSFORMATION PRODUCTIVE ET AGRO INDUSTRIELLE', 'pilier', null, 2_907_120, 1_000_000, 1_907_120),
    $L('2041', 'AXE 1 : Agriculture, Developpement Rural et Securite Alimentaire et Nutritionnelle (ADR/SAN)', 'axe', '204', 855_020, 340_000, 515_020),
    $L('20411', "Elaboration des politiques et strategies regionales dans le domaine de l'agriculture, du developpement rural et de la securite alimentaire et nutritionnelle", 'produit', '2041', 775_000, 295_000, 480_000),
    $L('20412', 'Renforcement de la coordination des politiques, strategies, programmes et projets regionaux', 'produit', '2041', 80_020, 45_000, 35_020),

    $L('2042', 'AXE 2 : Environnement, Ressources Naturelles, Biodiversite et Changement Climatique', 'axe', '204', 2_052_100, 660_000, 1_392_100),
    $L('20421', "Elaboration et adoption des politiques et strategies regionales de gestion des ressources naturelles, des forets, de l'environnement, du climat et de gestion des risques de catastrophes", 'produit', '2042', 675_000, 185_000, 490_000),
    $L('20422', 'Renforcement de la coordination des politiques et la supervision technique des strategies, programmes et projets regionaux de gestion des ressources naturelles', 'produit', '2042', 85_000, 85_000, 0),
    $L('20423', 'Mise a niveau du secteur agrosylvopastoral et halieutique par rapport aux effets du changement climatique', 'produit', '2042', 390_000, 160_000, 230_000),
    $L('20424', 'Renforcement de la resilience des systemes ecologiques economiques et sociaux face aux aleas climatiques', 'produit', '2042', 702_100, 30_000, 672_100),
    $L('20425', "Appui a la mise en place des dispositifs de Recherche, Innovation, Digitalisation et Partenariats dans les domaines de l'environnement, ressources naturelles, Biodiversite et changement climatique", 'produit', '2042', 200_000, 200_000, 0),

    // ============================ PILIER 5 ============================
    $L('205', 'PILIER 5 - PROMOTION DU GENRE, INTEGRATION HUMAINE ET SOCIALE', 'pilier', null, 1_956_100, 1_350_000, 606_100),
    $L('2051', 'AXE 1 : RENFORCER LES SYSTEMES COMMUNAUTAIRES DE SANTE, DE PROTECTION SOCIALE ET DE RESILIENCE SANITAIRE', 'axe', '205', 1_026_100, 420_000, 606_100),
    $L('20511', "Operationnalisation d'un mecanisme regional de coordination des actions de la sante", 'produit', '2051', 533_600, 170_000, 363_600),
    $L('20512', 'Harmonisation des mecanismes de preparation, de surveillance et de riposte aux maladies a potentiel epidemique', 'produit', '2051', 297_500, 55_000, 242_500),
    $L('20513', 'Reduction des mortalites maternelle, neonatale et infanto-juvenile', 'produit', '2051', 5_000, 5_000, 0),
    $L('20514', 'Amelioration de la regulation et la facilitation des approvisionnements en medicaments et intrants de laboratoires dans la sous-region', 'produit', '2051', 65_000, 65_000, 0),
    $L('20515', 'Harmonisation des cadres, Programmes et Strategies de protection sociale', 'produit', '2051', 125_000, 125_000, 0),

    $L('2052', "AXE 2 : FACILITER L'INCLUSION SOCIO-ECONOMIQUE DES JEUNES ET PROMOUVOIR LES POLITIQUES COMMUNAUTAIRES EN MATIERE DE SPORT ET D'EMPLOI", 'axe', '205', 150_000, 150_000, 0),
    $L('20521', "Elaboration et adoption d'une politique/strategie regionale de l'emploi et de l'entreprenariat des jeunes", 'produit', '2052', 75_000, 75_000, 0),

    $L('2053', "AXE 3 : FACILITER L'INTEGRATION SYSTEMATIQUE DU GENRE DANS LES POLITIQUES COMMUNAUTAIRES ET L'AUTONOMISATION DES FEMMES", 'axe', '205', 110_000, 110_000, 0),
    $L('20531', 'Prise en compte du genre dans les politiques, programmes et institutions', 'produit', '2053', 0, 0, 0),
    $L('20532', 'Soutien aux femmes entrepreneures', 'produit', '2053', 110_000, 110_000, 0),

    $L('2054', 'AXE 4 : TRANSFORMER LES SYSTEMES EDUCATIFS, SCIENTIFIQUES, TECHNOLOGIQUES ET CULTURELS', 'axe', '205', 520_000, 520_000, 0),
    $L('20541', "Renforcement de la gouvernance regionale de l'education, de la science et de la technologie et harmonisation des politiques nationales", 'produit', '2054', 275_000, 275_000, 0),
    $L('20542', 'Renforcement des capacites de recherche, de production scientifique, innovation et de developpement culturel', 'produit', '2054', 140_000, 140_000, 0),
    $L('20543', "Renforcement de la contribution de l'education, de la science et de la technologie a l'economie du savoir", 'produit', '2054', 105_000, 105_000, 0),

    $L('2055', "AXE 5 : RENFORCER LES CADRES DE GESTION DES MIGRATIONS, DE PROTECTION DES REFUGIES ET D'ASILE", 'axe', '205', 150_000, 150_000, 0),
    $L('20551', "Renforcement et Harmonisation des cadres politiques, juridiques et institutionnels regionaux relatifs a la migration, a la mobilite de la main-d'oeuvre et aux deplacements forces", 'produit', '2055', 75_000, 75_000, 0),
    $L('20552', "Amelioration de l'acces a la protection, aux droits et aux services de base des migrants et des populations deplacees", 'produit', '2055', 50_000, 50_000, 0),
    $L('20553', "Renforcement de l'inclusion socio-economique, mobilite de la main-d'oeuvre et resilience des migrants, des populations deplacees et des communautes hotes", 'produit', '2055', 25_000, 25_000, 0),
    $L('20554', 'Promotion des solutions durables aux deplacements forces et a la migration contrainte', 'produit', '2055', 0, 0, 0),

    // ============================ PILIER 6 ============================
    $L('206', 'PILIER 6 - POURSUITE DE LA REFORME ET RENFORCEMENT DE LA GOUVERNANCE INSTITUTIONNELLE', 'pilier', null, 1_485_000, 1_485_000, 0),
    $L('2061', "AXE 1 : REVISER LES TEXTES FONDAMENTAUX ET ADOPTER LES TEXTES D'APPLICATION SUBSEQUENTS", 'axe', '206', 135_000, 135_000, 0),
    $L('20611', 'Amelioration de la gouvernance de la Commission', 'produit', '2061', 135_000, 135_000, 0),

    $L('2062', 'AXE 2 : AMELIORER LES CAPACITES OPERATIONNELLES (HUMAINES, MATERIELLES ET FINANCIERES) DE LA CEEAC', 'axe', '206', 362_000, 362_000, 0),
    $L('20621', 'Amelioration de la gestion des ressources humaines', 'produit', '2062', 85_000, 85_000, 0),
    $L('20622', 'Renforcement des capacites de mobilisation des ressources financieres pour assurer leur efficacite et leur durabilite', 'produit', '2062', 220_000, 220_000, 0),
    $L('20623', 'Amelioration de la gestion et la securisation des ressources', 'produit', '2062', 57_000, 57_000, 0),

    $L('2063', 'AXE 3 : RENFORCER LES MECANISMES DE COMMUNICATION, DE PLANIFICATION ET DE CONTROLE', 'axe', '206', 792_000, 792_000, 0),
    $L('20631', 'Amelioration des mecanismes de communication institutionnelle et de visibilite de la Communaute', 'produit', '2063', 182_000, 182_000, 0),
    $L('20632', 'Amelioration du systeme de planification et de budgetisation', 'produit', '2063', 520_000, 520_000, 0),
    $L('20634', 'Amelioration du systeme de controle', 'produit', '2063', 90_000, 90_000, 0),
    $L('20635', "Mise en place d'un controle financier apprecie en termes d'efficacite et d'efficience qui contribue a l'amelioration de la qualite de la depense communautaire", 'produit', '2063', 120_000, 120_000, 0),

    $L('2064', 'AXE 4 : ASSURER LA COORDINATION ET LE RENFORCEMENT DES CADRES DE COOPERATION ET DE MOBILISATION DES RESSOURCES', 'axe', '206', 196_000, 196_000, 0),
    $L('20641', "Renforcement de l'efficacite des politiques et mecanismes de cooperation", 'produit', '2064', 196_000, 196_000, 0),
];
