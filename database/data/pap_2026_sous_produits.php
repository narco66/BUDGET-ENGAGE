<?php

/**
 * Sous-produits (codes 6 chiffres) du Plan Annuel de Performance (PAP) 2026 de la CEEAC.
 *
 * Source : « BUDGET EXERCICE 2026 Final-2.pdf ».
 * Unite des montants : MILLIERS de FCFA (convertis en FCFA x1000 par le service d'import).
 * Chaque sous-produit reference le code de son produit parent (5 chiffres).
 *
 * Remarques de fidelite :
 *  - Les codes en doublon dans le document (OCR) sont suffixes par une lettre (ex : 201211b)
 *    pour garantir l'unicite, le libelle d'origine etant conserve.
 *  - Certaines sommes de sous-produits ne se reconcilient pas a leur produit parent : il s'agit
 *    d'incoherences du document source, signalees automatiquement en avertissements.
 *
 * @return array<int, array{code:string, libelle:string, niveau:string, parent:string, total:int|float, ceeac:int|float, ptf:int|float}>
 */

$S = static fn (string $code, string $libelle, string $parent, $total, $ceeac, $ptf): array => [
    'code' => $code,
    'libelle' => $libelle,
    'niveau' => 'sous_produit',
    'parent' => $parent,
    'total' => $total,
    'ceeac' => $ceeac,
    'ptf' => $ptf,
];

return [
    // ===================== PILIER 1 =====================
    $S('201121', "Elaboration, adoption et mise en oeuvre d'un cadre regional d'orientation sur la mediation", '20112', 135_000, 135_000, 0),
    $S('201122', 'Mise en place de la plateforme organique de mediation', '20112', 120_000, 120_000, 0),
    $S('201131', 'Elaboration, adoption et mise en oeuvre du Protocole sur la bonne gouvernance et la lutte contre la corruption', '20113', 50_000, 50_000, 0),
    $S('201132', "Mise en oeuvre du memorandum d'entente de la CEEAC avec le Mecanisme Africain de Verification par les Pairs (MAEP)", '20113', 0, 0, 0),
    $S('201133', 'Institutionalisation du Comite Technique Specialise (CTS) sur la gouvernance politique', '20113', 0, 0, 0),
    $S('201141', "Elaboration, adoption et mise en oeuvre du protocole sur la promotion de l'etat de droit et les droits de l'Homme", '20114', 0, 0, 0),
    $S('201142', 'Elaboration et adoption du Protocole sur la promotion de la Democratie et des elections', '20114', 0, 0, 0),
    $S('201143', "Observation electorale dans les Etats membres : deploiement des missions d'observation electorales", '20114', 200_000, 200_000, 0),
    $S('201151', "Revision, adoption et mise en oeuvre du Protocole sur la libre circulation et le droit d'etablissement", '20115', 0, 0, 0),
    $S('201161', "Elaboration, adoption et mise en oeuvre de la Strategie regionale de lutte contre les discours de haine et d'incitation a la violence en Afrique Centrale", '20116', 0, 0, 0),

    $S('201211', "Elaboration, adoption et mise en oeuvre de la Doctrine de la Force Multinationale de l'Afrique Centrale (FOMAC)", '20121', 60_000, 60_000, 0),
    $S('201212', 'Elaboration, adoption et mise en oeuvre des procedures operationnelles standards de la FOMAC', '20121', 0, 0, 0),
    $S('201213', "Elaboration et adoption de l'etude sur le repositionnement de l'Etat-Major Regional de la FOMAC au sein de la Commission de la CEEAC", '20121', 50_000, 50_000, 0),
    $S('201214', 'Constitution du Catalogue des capacites de la FOMAC (memoranda, auto-verification, mise en oeuvre)', '20121', 0, 0, 0),
    $S('201215', 'Constitution, adoption, certification et mise en oeuvre de la Capacite de Deploiement Rapide (CDR) de la FOMAC', '20121', 0, 0, 0),
    $S('201216', 'Determination des attributions et Operationalisation du Centre Operationnel (CO) de la FOMAC', '20121', 60_000, 60_000, 0),
    $S('201217', 'Determination des attributions et Operationalisation du Centre Regional de Coordination de Mouvements (CRCM)', '20121', 10_000, 10_000, 0),
    $S('201218', 'Mise en place du Depot Logistique Regional (DLR)', '20121', 20_000, 20_000, 0),
    $S('201219', 'Organisation de deux exercices communautaires (planification, jeux, retour d\'experiences)', '20121', 190_000, 190_000, 0),
    $S('201210', "Mise en place de plans de formation et d'entrainement de la FOMAC", '20121', 0, 0, 0),
    $S('201211b', "Elaboration, adoption et mise en application du concept d'emploi de la FOMAC", '20121', 0, 0, 0),
    $S('201212b', 'Elaboration, adoption et mise en oeuvre de la politique regionale de defense', '20121', 0, 0, 0),

    $S('201221', 'Elaboration, adoption et mise en oeuvre du plan quinquennal de la Strategie regionale de lutte contre le terrorisme', '20122', 15_000, 15_000, 0),
    $S('201222', "Mise en oeuvre de la Convention de Kinshasa (appui aux Commissions Nationales ALPC, guide d'harmonisation)", '20122', 0, 0, 0),
    $S('201223', 'Mise en place du Centre Regional de Prevention et de Coordination de la lutte contre le Terrorisme', '20122', 55_000, 55_000, 0),

    $S('201231', "Consolidation du cadre regional de dialogue, d'action et de coordination autour de la gestion concertee des frontieres", '20123', 120_000, 120_000, 0),

    $S('201241', "Organisation d'un atelier pour identification des segments frontaliers non delimites/demarques", '20124', 0, 0, 0),
    $S('201242', 'Desenclavement des zones frontalieres', '20124', 0, 0, 0),
    $S('201243', 'Dynamisation des echanges transfrontaliers', '20124', 0, 0, 0),
    $S('201244', 'Securisation des espaces frontaliers', '20124', 40_000, 40_000, 0),
    $S('201245', 'Amelioration du vivre-ensemble et de la gouvernance locale', '20124', 20_000, 20_000, 0),
    $S('201246', 'Elaboration, adoption et mise en oeuvre du Protocole regional sur le pastoralisme et la transhumance transfrontaliere', '20124', 15_000, 15_000, 0),

    $S('201251', 'Creation d\'une base de donnees regionale des incidents maritimes (Protocole de collecte et validation)', '20125', 80_000, 80_000, 0),
    $S('201252', 'Consolidation du Cadre legal du CRESMAC et des Centres Multinationaux de coordination (CMC)', '20125', 0, 0, 0),
    $S('201253', 'Renforcement du mecanisme de "Command and Control" du CRESMAC (manuel, academie regionale)', '20125', 15_000, 15_000, 0),
    $S('201254', 'Renforcement de la surveillance maritime et dans les eaux continentales partagees', '20125', 20_000, 20_000, 0),
    $S('201255', 'Mise en oeuvre de la strategie de securisation maritime (COMAR 3 et 4, reunions du CIC)', '20125', 115_000, 115_000, 0),

    $S('201311', 'Elaboration, adoption et mise en oeuvre du cadre normatif et operationnel du MARAC', '20131', 0, 0, 0),
    $S('201312', "Production des rapports d'alerte rapide et presentation aux Instances et Partenaires", '20131', 10_000, 10_000, 0),
    $S('201313', 'Operationnalisation de la Salle de veille du MARAC (armement, equipements, capacites)', '20131', 90_000, 90_000, 0),

    $S('201321', 'Redynamisation du Reseau des Points focaux nationaux du MARAC', '20132', 65_000, 65_000, 0),
    $S('201322', 'Mise en place et operationnalisation des Bureaux Nationaux du MARAC', '20132', 0, 0, 0),
    $S('201323', 'Renforcement des capacites des Organisations de la Societe civile en prevention et resolution des conflits', '20132', 45_000, 45_000, 0),

    // ===================== PILIER 2 =====================
    $S('202111', 'Operationnalisation de la zone de libre-echange de la CEEAC', '20211', 100_000, 100_000, 0),
    $S('202112', "Operationnalisation du mecanisme d'administration des mesures de defense commerciale", '20211', 0, 0, 0),
    $S('202113', 'Mise en oeuvre effective du Tarif Exterieur Commun et du Code des Douanes harmonises CEEAC-CEMAC', '20211', 155_000, 155_000, 0),

    $S('202121', "Operationnalisation du mecanisme d'elimination des BNT", '20212', 60_000, 60_000, 0),
    $S('202122', 'Facilitation du commerce transfrontalier des marchandises', '20212', 140_000, 140_000, 0),

    $S('202131', 'Liberalisation du commerce des services au niveau de la CEEAC', '20213', 280_000, 50_000, 230_000),
    $S('202132', 'Operationnalisation de la Politique regionale du commerce numerique en Afrique centrale', '20213', 55_000, 20_000, 35_000),

    $S('202141', "Mise en oeuvre des accords de l'OMC et de tous les instruments multilateraux pertinents", '20214', 0, 0, 0),
    $S('202142', 'Mise en oeuvre de l\'accord ZLECAf et des autres instruments continentaux pertinents', '20214', 100_000, 50_000, 50_000),

    $S('202151', "Operationnalisation du cadre legal et reglementaire sur la libre-circulation des personnes et le droit d'etablissement", '20215', 50_000, 50_000, 0),
    $S('202152', 'Elaboration et mise en oeuvre de la politique migratoire de la CEEAC', '20215', 0, 0, 0),

    $S('202211', "Elaboration des feuilles de route pour accompagner les reformes pour l'amelioration de l'environnement des affaires", '20221', 110_000, 110_000, 0),
    $S('202212', 'Harmonisation et modernisation des cadres de promotion economique de la communaute', '20221', 50_000, 50_000, 0),

    $S('202221', "Appui a l'amelioration de la gouvernance economique", '20222', 115_000, 115_000, 0),

    $S('202231', "Operationnalisation du Fonds de Cooperation et de Developpement de l'Afrique centrale", '20223', 80_000, 80_000, 0),
    $S('202232', 'Mise en place du cadre de convergence macro-economique et des institutions financieres et monetaires', '20223', 15_000, 15_000, 0),

    $S('202311', 'Appui a la transformation structurelle des economies de la CEEAC autour des chaines de valeur prioritaires', '20231', 200_000, 0, 200_000),
    $S('202312', "Promotion d'initiatives collaboratives, de reseaux et de clusters au sein des CVP", '20231', 100_000, 0, 100_000),

    $S('202321', 'Appui a la modernisation et au developpement technologique des entreprises dans les CVP', '20232', 200_000, 0, 200_000),
    $S('202322', 'Operationnalisation du Centre regional de developpement industriel et technologique', '20232', 105_000, 75_000, 30_000),

    $S('202331', "Harmonisation et integration regionales de la gouvernance de l'infrastructure qualite, des politiques et des normes", '20233', 415_000, 65_000, 350_000),
    $S('202332', 'Harmonisation et integration regionales dans les fonctions liees aux mesures SPS et aux normes', '20233', 60_000, 10_000, 50_000),
    $S('202333', "Renforcement des capacites des institutions de l'IQ (niveau national/regional)", '20233', 0, 0, 0),

    $S('202341', 'Assistance technique et renforcement des capacites (propriete intellectuelle)', '20234', 50_000, 50_000, 0),
    $S('202342', 'Etablissement de normes et politiques nationales et regionales', '20234', 0, 0, 0),

    $S('202351', "Renforcement des cadres de dialogue public-prive sur le processus d'integration regionale", '20235', 110_000, 60_000, 50_000),
    $S('202352', 'Appuis aux initiatives de promotion economique et commerciale (FECIAC, SAIEF, label Made in Central Africa)', '20235', 250_000, 250_000, 0),

    $S('202411', 'Mise en place du Comite Regional de Gouvernance Statistique de la CEEAC', '20241', 65_000, 65_000, 0),
    $S('202412', 'Actualisation de la strategie regionale de developpement de la statistique', '20241', 90_000, 30_000, 60_000),
    $S('202413', 'Renforcement des capacites des INS et administrations sectorielles en gouvernance statistique', '20241', 0, 0, 0),
    $S('202414', 'Suivi du projet regional des statistiques de migration avec Statistics Sweden', '20241', 40_000, 0, 40_000),

    $S('202421', 'Harmonisation des methodologies de collecte et production des statistiques regionales', '20242', 0, 0, 0),
    $S('202422', "Developpement d'un modele regional de previsions economiques et financieres", '20242', 20_000, 20_000, 0),
    $S('202423', 'Mise en oeuvre d\'un programme regional annuel de production statistique', '20242', 50_000, 50_000, 0),
    $S('202424', 'Renforcement des capacites techniques en modelisation economique et previsions', '20242', 0, 0, 0),

    $S('202431', 'Creation et deploiement du Portail Statistique Regional de la CEEAC', '20243', 25_000, 25_000, 0),
    $S('202432', 'Publication des Bulletins Statistiques Regionaux et notes de conjoncture', '20243', 15_000, 15_000, 0),
    $S('202433', 'Standardisation des formats de diffusion statistique (SHaSA, SDMX)', '20243', 0, 0, 0),
    $S('202434', 'Organisation des ateliers regionaux de dissemination', '20243', 0, 0, 0),
    $S('202435', "Promotion de l'usage des statistiques pour la decision publique", '20243', 0, 0, 0),

    $S('202441', "Mise en place de l'Indice d'Integration Regionale de l'Afrique Centrale (aligne sur l'ASRI)", '20244', 0, 0, 0),
    $S('202442', 'Collecte et harmonisation des donnees necessaires au suivi de l\'integration regionale', '20244', 30_000, 30_000, 0),
    $S('202443', "Publication d'un Rapport Annuel sur l'Etat de l'Integration Regionale dans la CEEAC", '20244', 10_000, 10_000, 0),
    $S('202444', 'Renforcement de la coordination entre la CEEAC, l\'UA, la CEMAC et les organisations specialisees', '20244', 75_000, 75_000, 0),
    $S('202445', "Developpement d'un tableau de bord dynamique de suivi de l'integration regionale", '20244', 20_000, 20_000, 0),

    // ===================== PILIER 3 =====================
    $S('203111', "Elaboration d'une Politique et le plan d'action d'amenagement du territoire communautaire (PATC)", '20311', 50_000, 50_000, 0),
    $S('203112', "Adoption et operationnalisation d'un schema directeur de developpement de l'espace regional (SDER)", '20311', 0, 0, 0),

    $S('203121', 'Developpement des infrastructures transnationales de transport (routier, ferroviaire et fluvial)', '20312', 4_136_332, 105_000, 4_031_332),
    $S('203122', 'Elaboration et revision du dispositif institutionnel', '20312', 100_000, 100_000, 0),

    $S('203131', 'Amelioration de la connectivite des voies aeriennes', '20313', 2_622_204, 15_000, 2_607_204),
    $S('203132', 'Renforcement de la connectivite des voies maritimes', '20313', 20_000, 20_000, 0),

    $S('203211', "Mise en oeuvre du Cadre strategique regional integre pour le secteur de l'energie (renouvelables, efficacite, hydrocarbures)", '20321', 760_150, 0, 760_150),
    $S('203212', "Mise en place du dispositif juridique regional harmonise pour la cooperation energetique et la regulation de l'electricite", '20321', 485_375, 0, 485_375),
    $S('203213', "Renforcement de la Gouvernance et des capacites institutionnelles avec un systeme d'information energetique", '20321', 1_617_000, 220_000, 1_397_000),

    $S('203221', 'Developpement des infrastructures energetiques en promouvant les energies renouvelables', '20322', 10_000, 10_000, 0),
    $S('203222', 'Mise a niveau des projets energetiques transnationaux (mesures environnementales et climat)', '20322', 25_000, 25_000, 0),

    $S('203231', 'Mise en place des mecanismes et instruments de financement (investissements prives, solutions innovantes)', '20323', 0, 0, 0),
    $S('203232', 'Mise en place d\'un systeme de suivi et de coordination regionale des projets energetiques', '20323', 245_000, 245_000, 0),

    $S('203311', 'Elaboration des politiques et strategies regionales du secteur des TIC', '20331', 610_000, 60_000, 550_000),
    $S('203312', 'Harmonisation des legislations et les reglementations nationales des TIC', '20331', 251_000, 65_000, 186_000),

    $S('203321', 'Deploiement des infrastructures numeriques', '20332', 165_000, 10_000, 155_000),
    $S('203322', "Mise en place d'un cadre de gouvernance des projets d'infrastructures numeriques", '20332', 15_000, 15_000, 0),

    $S('203331', 'Soutien aux interconnexions transfrontalieres des Etats membres', '20333', 15_000, 15_000, 0),
    $S('203332', "Facilitation de l'acces aux services des TIC dans les zones rurales et sensibles au genre", '20333', 45_000, 45_000, 0),

    $S('203341', 'Signature des Accords de partenariat avec des organisations partenaires', '20334', 0, 0, 0),
    $S('203342', "Harmonisation des points de vue de l'Afrique centrale dans le secteur des TIC (reunions internationales)", '20334', 5_000, 5_000, 0),
    $S('203343', 'Participation de la CEEAC aux reunions internationales', '20334', 20_000, 20_000, 0),

    // ===================== PILIER 4 =====================
    $S('204111', 'Elaboration et adoption de la PAC', '20411', 50_000, 50_000, 0),
    $S('204112', 'Elaboration et adoption de la PRIA', '20411', 110_000, 60_000, 50_000),
    $S('204113', 'Elaboration et adoption des PNIA des Etats membres au niveau national', '20411', 430_000, 185_000, 245_000),
    $S('204114', "Elaboration et adoption de la Strategie Regionale de Securite Alimentaire et Nutritionnelle (SRSAN-AC)", '20411', 85_000, 0, 85_000),
    $S('204115', 'Elaboration et adoption de la Strategie Regionale de Developpement de la Riziculture (SRDR-AC)', '20411', 25_000, 0, 25_000),
    $S('204116', 'Elaboration et adoption de la Strategie Regionale d\'Eradication de la Peste des Petits Ruminants (SRE/PPR-AC)', '20411', 25_000, 0, 25_000),
    $S('204117', "Elaboration et adoption de la Strategie Regionale de Developpement de la Peche et de l'Aquaculture (SRDPA-AC)", '20411', 25_000, 0, 25_000),

    $S('204121', 'Operationnalisation du DERNADR et ses Instances de decisions', '20412', 35_000, 20_000, 15_000),
    $S('204122', 'Operationnalisation du dispositif de Suivi-Evaluation du DERNADR', '20412', 45_020, 25_000, 20_020),

    $S('204211', 'Elaboration et adoption de la PEC', '20421', 140_000, 90_000, 50_000),
    $S('204212', 'Le PFR est elabore, valide et adopte', '20421', 90_000, 0, 90_000),
    $S('204213', 'Elaboration et adoption de la Strategie Regionale de Gestion des Risques de Catastrophes et adaptation au Changement Climatique (SRGRC-ACC)', '20421', 75_000, 20_000, 55_000),
    $S('204214', 'Harmonisation des politiques et strategies nationales des Etats membres avec la PEC et la PFR', '20421', 105_000, 50_000, 55_000),
    $S('204215', 'Renforcement des capacites de preparation et de reponse aux urgences (EP&R) de la CEEAC et de ses Etats membres', '20421', 240_000, 0, 240_000),
    $S('204216', 'Elaboration et adoption de la PRE revisee', '20421', 25_000, 25_000, 0),

    $S('204221', 'Amelioration de la coordination des politiques et la supervision technique des strategies, programmes et projets regionaux de gestion', '20422', 0, 0, 0),
    $S('204222', 'Elaboration de la feuille de route pour le PARGIRE et integration du GIREE dans les politiques', '20422', 50_000, 50_000, 0),
    $S('204223', 'Renforcement du cadre institutionnel de coordination pour la gestion des ressources en eaux', '20422', 35_000, 35_000, 0),

    $S('204231', 'La resilience des exploitations agricoles et des ecosystemes est amelioree', '20423', 125_000, 10_000, 115_000),
    $S('204232', 'Reduction de 50% des pertes post-recolte et des emissions agrosylvopastorales et halieutiques', '20423', 115_000, 0, 115_000),
    $S('204233', "Deploiement d'un cadre regional AIC operationnel et fonctionnel dans 11 Etats membres", '20423', 150_000, 150_000, 0),

    $S('204241', 'Elaboration d\'un cadre regional harmonise pour la mise en oeuvre et le suivi des politiques climatiques dans les EM', '20424', 487_100, 10_000, 477_100),
    $S('204242', "Appuis aux Etats membres pour un mecanisme de financement innovant des politiques climatiques", '20424', 215_000, 20_000, 195_000),

    $S('204251', 'Digitalisation des services agrosylvopastoraux, environnementaux, climatologiques et meteorologiques', '20425', 100_000, 100_000, 0),
    $S('204252', 'Digitalisation du Potentiel Solaire et Eolien dans les Etats Membres de la CEEAC', '20425', 50_000, 50_000, 0),
    $S('204253', 'Digitalisation de la dynamique de la desertification et de la degradation de la biodiversite et des sols', '20425', 50_000, 50_000, 0),
    $S('204254', 'Renforcement des connaissances a des fins de valorisation socio-economique des ressources en eaux', '20425', 0, 0, 0),

    // ===================== PILIER 5 =====================
    $S('205111', 'Realisation d\'une revue situationnelle du Systeme de Sante des pays de la Region', '20511', 126_000, 0, 126_000),
    $S('205112', 'Elaboration des documents de politique et de strategie regionale de la sante', '20511', 200_800, 0, 200_800),
    $S('205113', "Operationnalisation de l'Organisation de la Sante en Afrique Centrale", '20511', 135_000, 135_000, 0),
    $S('205114', 'Promotion du concept "une seule sante" au niveau regional et dans tous les Etats de la CEEAC', '20511', 35_000, 35_000, 0),
    $S('205115', "Operationnalisation d'un mecanisme regional pour la recherche en pharmacopee traditionnelle", '20511', 36_800, 0, 36_800),

    $S('205121', "Mise en oeuvre d'un plan regional de preparation et de riposte aux urgences sanitaires en Afrique centrale", '20512', 25_000, 25_000, 0),
    $S('205122', "Operationnalisation d'un mecanisme regional pour la cooperation inter-pays contre les maladies a potentiel epidemique", '20512', 30_000, 30_000, 0),
    $S('205123', "Mise en place d'un dispositif regional d'appui technique pour le renforcement des capacites en surveillance", '20512', 135_000, 0, 135_000),
    $S('205124', "Operationnalisation d'un reseautage des laboratoires de biologie humaine et animale de reference", '20512', 41_000, 0, 41_000),

    $S('205131', "Mise en place d'un mecanisme regional pour la promotion de l'audit des deces maternels", '20513', 5_000, 5_000, 0),
    $S('205132', "Mise en place d'un mecanisme regional de collaboration interprofessionnelle pour les equipements hospitaliers et la SRMNIA", '20513', 0, 0, 0),

    $S('205141', "Mise en place d'un dispositif regional de regulation des approvisionnements en medicaments et intrants de laboratoires", '20514', 0, 0, 0),
    $S('205142', 'Amelioration du dispositif regional pour faciliter la production et les approvisionnements en medicaments essentiels', '20514', 65_000, 65_000, 0),

    $S('205151', "Mise en place d'un cadre de gouvernance pour la Strategie integree Protection Sociale (PS) et AGR", '20515', 50_000, 50_000, 0),
    $S('205152', "Promotion et protection des Droits de l'Enfant en Afrique centrale", '20515', 40_000, 40_000, 0),
    $S('205153', 'Appui a la Societe Civile et renforcement des partenariats et la cohesion communautaire', '20515', 35_000, 35_000, 0),

    $S('205211', "Mise en place d'un cadre regional de certification", '20521', 75_000, 75_000, 0),
    $S('205212', "Mise en place d'un Institut communautaire des sports", '20521', 0, 0, 0),
    $S('205213', "Amelioration de l'acces des jeunes a des emplois decents", '20521', 0, 0, 0),
    $S('205214', 'Dynamisation et mise en oeuvre du programme des jeunes volontaires de la CEEAC', '20521', 0, 0, 0),

    $S('205311', "Harmonisation et mise en oeuvre des Strategies pour l'egalite de genre publiques", '20531', 0, 0, 0),
    $S('205312', 'Participation des femmes a la vie publique et mise en oeuvre du Plan d\'action regional de R1325', '20531', 0, 0, 0),

    $S('205321', 'Promotion des reseaux de femmes entrepreneures', '20532', 35_000, 35_000, 0),
    $S('205322', 'Renforcement des mecanismes contre les violences basees sur le genre (VBG)', '20532', 75_000, 75_000, 0),

    $S('205411', 'Elaboration et adoption des cadres sectoriels integres de gouvernance regionale en Education, Formation, Sciences, Technologie et Innovation', '20541', 175_000, 175_000, 0),
    $S('205412', 'Elaboration et adoption des cadres sectoriels integres de gouvernance regionale pour la preservation et le developpement culturel', '20541', 100_000, 100_000, 0),

    $S('205421', "Etablissement des poles d'excellence regionaux et des centres de reference dotes d'infrastructures modernes", '20542', 25_000, 25_000, 0),
    $S('205422', 'Renforcement des capacites de recherche, production scientifique et innovation', '20542', 80_000, 80_000, 0),
    $S('205423', 'Renforcement des capacites de recherche, de creation et de production dans la culture, le patrimoine et les arts', '20542', 35_000, 35_000, 0),

    $S('205431', 'Renforcement des capacites de recherche, production scientifique et innovation', '20543', 45_000, 45_000, 0),
    $S('205432', "Etablissement des partenariats regionaux multi-acteurs generateurs de projets d'innovation", '20543', 30_000, 30_000, 0),
    $S('205433', 'Operationnalisation des mecanismes regionaux de cooperation scientifique et educative', '20543', 30_000, 30_000, 0),
    $S('205434', 'Lancement des programmes regionaux d\'echanges academiques et scientifiques', '20543', 0, 0, 0),

    $S('205511', 'Renforcement et harmonisation des cadres politiques, juridiques et institutionnels regionaux relatifs a la migration', '20551', 65_000, 65_000, 0),
    $S('205512', 'Renforcement et harmonisation des cadres politiques, juridiques et institutionnels (volet complementaire)', '20551', 0, 0, 0),
    $S('205513', "Mise en place des Systemes nationaux de gestion des migrations, de l'asile et de la protection", '20551', 0, 0, 0),
    $S('205514', "Mise en place d'un cadre regional formalise de concertation interinstitutionnelle sur la migration", '20551', 0, 0, 0),
    $S('205515', "Elaboration et diffusion d'un rapport regional periodique sur les dynamiques migratoires", '20551', 0, 0, 0),
    $S('205516', "Mise en place et operationnalisation des mecanismes regionaux d'echange d'informations et d'alerte precoce", '20551', 0, 0, 0),
    $S('205517', 'Organisation de consultations regionales regulieres entre les Etats membres sur la migration', '20551', 0, 0, 0),
    $S('205518', 'Renforcement de la coordination entre la CEEAC, les autorites locales, les partenaires sociaux et techniques', '20551', 10_000, 10_000, 0),
    $S('205519', 'Promotion des approches regionales communes en matiere de protection des migrants et populations deplacees', '20551', 0, 0, 0),

    $S('205521', "Appuis aux Etats membres dans la mise en oeuvre de mecanismes d'identification, d'enregistrement et de documentation", '20552', 50_000, 50_000, 0),
    $S('205522', 'Renforcement des mecanismes de protection des groupes a besoins specifiques (femmes, enfants, handicapes, apatridie)', '20552', 0, 0, 0),
    $S('205523', 'Soutien au developpement de programmes de protection communautaire et de cohesion sociale', '20552', 0, 0, 0),
    $S('205524', 'Integration des dimensions genre, protection et droits humains dans les politiques migratoires', '20552', 0, 0, 0),

    $S('205531', "Promotion des programmes regionaux d'autonomisation economique des migrants et communautes hotes", '20553', 0, 0, 0),
    $S('205532', 'Elaboration de strategies nationales et regionales de solutions durables', '20553', 25_000, 25_000, 0),

    $S('205541', 'Mise en place et operationnalisation des mecanismes regionaux favorisant le retour volontaire, sur et digne', '20554', 0, 0, 0),

    // ===================== PILIER 6 =====================
    $S('206111', "Elaboration et adoption des textes et outils d'application des textes fondamentaux", '20611', 10_000, 10_000, 0),
    $S('206112', "Revision des Textes d'application conformement aux Textes fondamentaux", '20611', 40_000, 40_000, 0),
    $S('206113', 'Adoption et vulgarisation du manuel de procedures administratives, comptables et financieres', '20611', 15_000, 15_000, 0),
    $S('206114', 'Finalisation du processus juridique de creation de certaines structures et mecanismes existants', '20611', 70_000, 70_000, 0),

    $S('206211', 'Adoption et Mise en oeuvre de la Politique de Gestion des Ressources Humaines', '20621', 75_000, 75_000, 0),
    $S('206212', 'Amelioration de la Gestion du Personnel', '20621', 10_000, 10_000, 0),

    $S('206221', 'Mise en oeuvre et suivi du mecanisme de la CCI dans les Etats membres', '20622', 115_000, 115_000, 0),
    $S('206222', 'Amelioration de la gestion comptable des ressources', '20622', 105_000, 105_000, 0),
    $S('206223', 'Mise en place des mecanismes de financement innovants de la Communaute', '20622', 0, 0, 0),

    $S('206231', 'Amelioration des mecanismes de gestion du patrimoine immobilier et mobilier', '20623', 40_000, 40_000, 0),
    $S('206232', 'Amelioration du systeme de gestion de la securite des ressources humaines', '20623', 0, 0, 0),
    $S('206233', "Mise en place au sein de l'institution d'une infrastructure des systemes d'information conforme aux standards", '20623', 0, 0, 0),
    $S('206234', 'Reduction des incidents et amelioration des supports et la qualite de service', '20623', 12_000, 12_000, 0),
    $S('206235', "Mise en place d'un cadre de gouvernance des Systemes d'Information structure", '20623', 5_000, 5_000, 0),

    $S('206311', 'Adoption de la politique et la strategie de communication', '20631', 17_000, 17_000, 0),
    $S('206312', "Fourniture des services de traduction et d'interpretariat dans toutes les langues de la communaute", '20631', 55_000, 55_000, 0),
    $S('206313', 'Modernisation de la gestion documentaire', '20631', 110_000, 110_000, 0),

    $S('206321', "Mise en place d'un cadre et des outils performants pour la planification et la budgetisation", '20632', 185_000, 185_000, 0),
    $S('206322', 'Amelioration de la coordination des programmes et projets', '20632', 95_000, 95_000, 0),
    $S('206323', 'Mise en place des mecanismes de suivi-evaluation', '20632', 120_000, 120_000, 0),

    $S('206341', 'Amelioration du systeme de reddition des comptes', '20634', 60_000, 60_000, 0),
    $S('206342', "Mise en place du Comite d'Audit", '20634', 30_000, 30_000, 0),

    $S('206351', 'Renforcement des capacites organisationnelles et humaines du CFC', '20635', 0, 0, 0),
    $S('206352', "Accompagnement de l'Ordonnateur dans le cadre de l'execution du Budget en vue de la soutenabilite budgetaire", '20635', 120_000, 120_000, 0),

    $S('206411', 'Mise en place des politiques et mecanismes de cooperation et de coordination', '20641', 20_000, 20_000, 0),
    $S('206412', "Acceleration du processus de rationalisation des CERs operant dans l'integration regionale en Afrique Centrale", '20641', 11_000, 11_000, 0),
    $S('206413', 'Mise en place et renforcement des mecanismes de mobilisation des ressources extraordinaires', '20641', 50_000, 50_000, 0),
    $S('206414', 'Mise en place et operationnalisation des institutions et des mecanismes communautaires', '20641', 115_000, 115_000, 0),
];
