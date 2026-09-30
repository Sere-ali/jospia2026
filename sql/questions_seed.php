<?php
/**
 * Banque de 20 questions (test d'entrée /20) — modifiable depuis
 * l'espace Super Admin (admin/questions.php) après installation.
 * Format : [categorie, enonce, option_a, option_b, option_c, option_d, bonne_reponse]
 */
$questionsSeed = [
    // ---- Connaissance du Coran (4) ----
    ['Connaissance du Coran', "Quelle est la première sourate du Coran ?", "Al-Baqara", "Al-Fatiha", "Al-Ikhlas", "An-Nas", "B"],
    ['Connaissance du Coran', "Combien de sourates compte le Coran ?", "100", "104", "114", "120", "C"],
    ['Connaissance du Coran', "La sourate Al-Ikhlas traite principalement :", "Du jeûne", "De l'unicité d'Allah (Tawhid)", "Du pèlerinage", "De l'héritage", "B"],
    ['Connaissance du Coran', "Sur quel mont la révélation du Coran a-t-elle commencé ?", "Mont Uhud", "Mont Arafat", "Mont Hira", "Mont Sinaï", "C"],

    // ---- Jurisprudence islamique / Fiqh (4) ----
    ['Jurisprudence islamique', "Combien de prières obligatoires compte une journée en Islam ?", "3", "4", "5", "6", "C"],
    ['Jurisprudence islamique', "Quel acte annule les ablutions (wudu) ?", "Manger du pain", "Dormir profondément", "Parler", "Marcher", "B"],
    ['Jurisprudence islamique', "Le jeûne du mois de Ramadan est un pilier :", "Facultatif", "Obligatoire (fard)", "Réservé aux savants", "Réservé aux hommes", "B"],
    ['Jurisprudence islamique', "La Zakat est prélevée sur :", "Le temps libre", "Les biens atteignant le Nissab", "Les enfants", "Les prières", "B"],

    // ---- Dogme / Aqida (4) ----
    ['Dogme (Aqida)', "Combien de piliers compte la foi (Iman) selon la tradition la plus connue ?", "4", "5", "6", "7", "C"],
    ['Dogme (Aqida)', "Le Tawhid signifie :", "La prière collective", "L'unicité d'Allah", "Le jeûne", "L'aumône", "B"],
    ['Dogme (Aqida)', "Croire aux anges fait partie :", "Des piliers de l'Islam", "Des piliers de la foi", "Des recommandations", "D'aucune obligation", "B"],
    ['Dogme (Aqida)', "Le dernier des prophètes envoyés à l'humanité est :", "Moussa (Moïse)", "Issa (Jésus)", "Muhammad ﷺ", "Ibrahim (Abraham)", "C"],

    // ---- Culture générale islamique et académique (4) ----
    ['Culture générale', "Dans quelle ville se trouve la Mosquée Al-Aqsa ?", "La Mecque", "Médine", "Jérusalem (Al-Qods)", "Bagdad", "C"],
    ['Culture générale', "L'Hégire correspond à :", "La naissance du Prophète ﷺ", "L'émigration de La Mecque vers Médine", "La révélation du premier verset", "La conquête de La Mecque", "B"],
    ['Culture générale', "La capitale administrative de la Côte d'Ivoire est :", "Abidjan", "Bouaké", "Yamoussoukro", "San Pedro", "C"],
    ['Culture générale', "Un raisonnement qui va du général au particulier est dit :", "Inductif", "Déductif", "Analogique", "Statistique", "B"],

    // ---- Gestion associative (basé sur l'AEEMCI) (4) ----
    ['Gestion associative (AEEMCI)', "Que signifie le sigle AEEMCI ?", "Association des Élèves et Étudiants Musulmans de Côte d'Ivoire", "Alliance des Écoles Musulmanes de Côte d'Ivoire", "Association des Étudiants Musulmans du Continent Ivoirien", "Aucune de ces réponses", "A"],
    ['Gestion associative (AEEMCI)', "Dans une association, l'organe qui contrôle la gestion financière est généralement :", "Le bureau exécutif", "Le commissariat aux comptes / contrôle financier", "Le service communication", "Aucun organe", "B"],
    ['Gestion associative (AEEMCI)', "Un procès-verbal (PV) de réunion sert à :", "Décorer le local", "Garder une trace écrite des décisions prises", "Remplacer le règlement intérieur", "Rien de particulier", "B"],
    ['Gestion associative (AEEMCI)', "Le premier responsable d'une commission au sein d'une organisation est souvent appelé :", "Trésorier", "Président / Coordonnateur de commission", "Simple membre", "Invité", "B"],
];
