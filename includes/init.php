<?php
/**
 * Initialisation "silencieuse" : connexion DB, session, fonctions utilitaires.
 * Ne produit AUCUNE sortie HTML (contrairement à includes/header.php).
 * Permet aux pages qui ont besoin de $pdo / exigerRole() / etc. AVANT
 * de définir $titrePage (ex. pages admin qui chargent des données pour
 * construire le titre) de le faire sans casser le <title> de la page.
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

dortoirsRenommer($pdo);
sectionsRenommer($pdo);
paiementsPreparer($pdo);
journalAutomatique($pdo);
